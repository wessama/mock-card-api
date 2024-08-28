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
use Symfony\Component\Validator\ConstraintViolationListInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class TransactionApiController extends AbstractController
{
    public function __construct(
        private readonly PaymentHandlerProvider $paymentHandlerProvider,
        protected SerializerInterface $serializer,
        protected ValidatorInterface $validator
    ) {
    }

    #[Route('/api/transaction/{type}', name: 'api_transaction', methods: ['POST'])]
    public function handleTransactionAction(Request $request, string $type, array $supportedPaymentTypes): JsonResponse
    {
        // Validate that the type is supported
        if (!in_array(strtolower($type), $supportedPaymentTypes)) {
            throw new BadRequestHttpException("Unsupported payment type: {$type}");
        }

        // Deserialize JSON into DTO
        $transactionRequest = $this->serializer->deserialize(
            $request->getContent(),
            TransactionRequestDto::class,
            'json',
            ['groups' => ['api_write']]
        );

        // Validate DTO
        $errors = $this->validateData($transactionRequest);

        if (null !== $errors) {
            return $errors;
        }

        $handler = $this->paymentHandlerProvider->getHandler($type);
        $response = $handler->processTransaction($transactionRequest);

        // Serialize the response DTO with the 'api_read' group
        $jsonResponse = $this->serializer->serialize($response,
            'json',
            ['groups' => ['api_read']]
        );

        // Return the serialized response
        return new JsonResponse($jsonResponse, Response::HTTP_OK, [], true);
    }

    protected function validateData($data): ?JsonResponse
    {
        $errors = $this->validator->validate($data);

        if (count($errors) > 0) {
            return $this->json($this->formatValidationErrors($errors), Response::HTTP_BAD_REQUEST);
        }

        return null;
    }

    private function formatValidationErrors(ConstraintViolationListInterface $errors): array
    {
        $formattedErrors = [];

        foreach ($errors as $error) {
            $formattedErrors[] = [
                'field' => $error->getPropertyPath(),
                'message' => $error->getMessage(),
            ];
        }

        return ['errors' => $formattedErrors];
    }
}
