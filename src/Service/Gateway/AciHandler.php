<?php

namespace App\Service\Gateway;

use App\Dto\TransactionRequestDto;
use App\Dto\TransactionResponseDto;
use App\Service\PaymentHandlerInterface;
use Psr\Log\LoggerInterface;
use Symfony\Contracts\HttpClient\Exception\ClientExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\DecodingExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\RedirectionExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\ServerExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class AciHandler implements PaymentHandlerInterface
{
    protected string $authToken;
    protected string $entityId;

    public function __construct(
        protected string $apiUrl,
        private readonly HttpClientInterface $httpClient,
        private readonly LoggerInterface $logger,
    ) {
    }

    public function setEntityId(string $entityId): void
    {
        $this->entityId = $entityId;
    }

    public function setAuthToken(string $authToken): void
    {
        $this->authToken = $authToken;
    }

    /**
     * @throws TransportExceptionInterface
     * @throws ServerExceptionInterface
     * @throws RedirectionExceptionInterface
     * @throws DecodingExceptionInterface
     * @throws ClientExceptionInterface
     * @throws \Exception
     */
    public function processTransaction(TransactionRequestDto $transactionData): TransactionResponseDto
    {
        // Request data
        $data = [
            'entityId' => $this->entityId,
            'amount' => number_format($transactionData->amount, 2, '.', ''),
            'currency' => $transactionData->currency,
            'paymentBrand' => 'VISA',
            'paymentType' => 'DB',
            'card.number' => $transactionData->cardNumber,
            'card.holder' => $transactionData->cardHolderName,
            'card.expiryMonth' => $transactionData->cardExpMonth,
            'card.expiryYear' => $transactionData->cardExpYear,
            'card.cvv' => $transactionData->cardCvv,
        ];

        try {
            // Make the HTTP request
            $response = $this->httpClient->request('POST', "{$this->apiUrl}/v1/payments", [
                'headers' => [
                    'Authorization' => 'Bearer '.$this->authToken,
                ],
                'body' => $data,
            ]);

            $data = $response->toArray();
        } catch (ClientExceptionInterface $e) {
            $errorContent = $e->getResponse()->getContent(false);

            $this->logger->error('Error processing transaction', [
                'status_code' => $e->getCode(),
                'error' => $errorContent,
            ]);

            throw $e;
        }

        return new TransactionResponseDto(
            timestamp: $data['timestamp'] ?? null,
            transactionId: $data['id'] ?? null,
            bin: $data['card']['bin'] ?? null,
            amount: $data['amount'] ?? 0,
            currency: $data['currency'] ?? null
        );
    }
}
