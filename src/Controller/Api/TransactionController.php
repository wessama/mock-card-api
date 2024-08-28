<?php

namespace App\Controller\Api;

use App\Dto\TransactionRequestDto;
use App\Service\PaymentHandlerProvider;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class TransactionController extends AbstractController
{
    public function __construct(
        private readonly PaymentHandlerProvider $paymentHandlerProvider,
        private readonly SerializerInterface $serializer,
        private readonly ValidatorInterface $validator
    ) {
    }

    #[Route('/api/transaction/{type}', name: 'api_transaction', methods: ['POST'])]
    public function handleTransactionAction(Request $request, string $type, array $supportedPaymentTypes): JsonResponse
    {
        // Validate that the type is supported
        if (!in_array(strtolower($type), $supportedPaymentTypes)) {
            throw new BadRequestHttpException("Unsupported payment type: {$type}");
        }

        $transactionData = $request->request->all();

        // Deserialize JSON into DTO
        $transactionRequest = $this->serializer->deserialize(
            $request->getContent(),
            TransactionRequestDto::class,
            'json'
        );

        // Validate DTO
        $errors = $this->validator->validate($transactionRequest);

        if (count($errors) > 0) {
            $errorMessages = [];
            foreach ($errors as $error) {
                $errorMessages[] = $error->getPropertyPath().': '.$error->getMessage();
            }

            return new JsonResponse(['errors' => $errorMessages], Response::HTTP_BAD_REQUEST);
        }

        $handler = $this->paymentHandlerProvider->getHandler($type);
        $response = $handler->processTransaction($transactionData);

        return new JsonResponse($response);
    }
}
