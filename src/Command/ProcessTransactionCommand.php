<?php

namespace App\Command;

use App\Dto\TransactionRequestDto;
use App\Service\PaymentHandlerProvider;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Contracts\HttpClient\Exception\ClientExceptionInterface;

#[AsCommand(
    name: 'process:transaction',
    description: 'Processes a Shift4 or ACI transaction and outputs the result.',
)]
class ProcessTransactionCommand extends Command
{
    private array $supportedPaymentTypes;

    public function __construct(
        private readonly PaymentHandlerProvider $paymentHandlerProvider,
        private readonly SerializerInterface    $serializer,
        private readonly ValidatorInterface     $validator,
        string $supportedPaymentTypes
    ) {
        parent::__construct();

        $this->supportedPaymentTypes = explode(',', $supportedPaymentTypes);
    }

    protected function configure(): void
    {
        $this
            ->addArgument('type', InputArgument::REQUIRED, 'The payment gateway type (shift4 or aci)')
            ->addOption('amount', null, InputOption::VALUE_REQUIRED, 'Transaction amount (e.g., 92.00)')
            ->addOption('currency', null, InputOption::VALUE_REQUIRED, 'Currency code (e.g., EUR)')
            ->addOption('card-number', null, InputOption::VALUE_REQUIRED, 'Card number')
            ->addOption('card-holder', null, InputOption::VALUE_REQUIRED, 'Card holder name')
            ->addOption('card-exp-month', null, InputOption::VALUE_REQUIRED, 'Card expiration month (e.g., 05)')
            ->addOption('card-exp-year', null, InputOption::VALUE_REQUIRED, 'Card expiration year (e.g., 2034)')
            ->addOption('card-cvv', null, InputOption::VALUE_REQUIRED, 'Card CVV')
            ->setHelp(<<<EOT
The <info>%command.name%</info> command processes a transaction using either Shift4 or ACI gateway.

Usage:

  <info>php %command.full_name% shift4 --amount=92.00 --currency=EUR --card-number=4200000000000000 --card-holder="Jane Jones" --card-exp-month=05 --card-exp-year=2034 --card-cvv=123</info>

Arguments:

  <info>type</info>            The payment gateway type (shift4 or aci)

Options:

  <info>--amount</info>          Transaction amount (e.g., 150.75)
  <info>--currency</info>        Currency code (e.g., EUR)
  <info>--card-number</info>     Card number
  <info>--card-holder</info>     Card holder name
  <info>--card-exp-month</info>  Card expiration month (e.g., 05)
  <info>--card-exp-year</info>   Card expiration year (e.g., 2025)
  <info>--card-cvv</info>        Card CVV
EOT
            );
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $type = $input->getArgument('type');

        // Validate that the type is supported
        if (!in_array(strtolower($type), $this->supportedPaymentTypes)) {
            $io->error("Unsupported payment type: {$type}");
            return Command::FAILURE;
        }

        // Create TransactionRequestDto from input options
        $transactionRequest = new TransactionRequestDto();
        $transactionRequest->amount = (float) $input->getOption('amount');
        $transactionRequest->currency = $input->getOption('currency');
        $transactionRequest->cardNumber = $input->getOption('card-number');
        $transactionRequest->cardHolderName = $input->getOption('card-holder');
        $transactionRequest->cardExpMonth = $input->getOption('card-exp-month');
        $transactionRequest->cardExpYear = $input->getOption('card-exp-year');
        $transactionRequest->cardCvv = $input->getOption('card-cvv');

        // Validate the DTO
        $errors = $this->validator->validate($transactionRequest);

        if (count($errors) > 0) {
            foreach ($errors as $error) {
                $io->error($error->getPropertyPath() . ': ' . $error->getMessage());
            }
            return Command::FAILURE;
        }

        try {
            $handler = $this->paymentHandlerProvider->getHandler($type);
            $response = $handler->processTransaction($transactionRequest);

            // Serialize the response DTO to JSON
            $jsonResponse = $this->serializer->serialize($response, 'json', ['groups' => ['console_read']]);

            $io->success('Transaction processed successfully.');
            $io->writeln($jsonResponse);
            return Command::SUCCESS;
        } catch (ClientExceptionInterface $e) {
            $io->error('Error processing transaction: ' . $e->getMessage());
            return Command::FAILURE;
        }
    }
}
