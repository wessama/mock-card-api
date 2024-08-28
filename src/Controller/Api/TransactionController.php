<?php

namespace App\Controller\Api;

use App\Dto\TransactionRequestDto;
use App\Dto\TransactionResponseDto;
use App\Service\PaymentHandlerProvider;
use Nelmio\ApiDocBundle\Annotation\Model;
use Nelmio\ApiDocBundle\Annotation\Security;
use OpenApi\Attributes as OA;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Validator\ConstraintViolationListInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class TransactionController extends AbstractController
{
    public array $supportedPaymentTypes;

    public function __construct(
        private readonly PaymentHandlerProvider $paymentHandlerProvider,
        protected SerializerInterface $serializer,
        protected ValidatorInterface $validator,
        string $supportedPaymentTypes
    ) {
        $this->supportedPaymentTypes = explode(',', $supportedPaymentTypes);
    }

    #[Route('/api/transaction/{type}', name: 'api_transaction', methods: ['POST'])]
    #[OA\Post(
        path: '/api/transaction/{type}',
        description: 'Processes a transaction for the specified payment type',
        summary: 'Process a transaction',
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(ref: new Model(type: TransactionRequestDto::class, groups: ['api_write']))
        ),
        parameters: [
            new OA\Parameter(
                name: 'type',
                description: 'The payment gateway type (shift4 or aci)',
                in: 'path',
                required: true,
                schema: new OA\Schema(type: 'string', enum: ['shift4', 'aci'])
            ),
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Successful response',
                content: new OA\JsonContent(ref: new Model(type: TransactionResponseDto::class, groups: ['api_read']))
            ),
            new OA\Response(
                response: 400,
                description: 'Bad request - Unsupported payment type or validation error',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'error', type: 'string'),
                    ],
                    type: 'object'
                )
            ),
            new OA\Response(
                response: 500,
                description: 'Internal server error'
            ),
        ]
    )]
    #[OA\Tag(name: 'Transactions')]
    #[Security(name: 'Bearer')]
    /**
     * @param array<string> $supportedPaymentTypes
     */
    public function handleTransactionAction(Request $request, string $type): JsonResponse
    {
        // Validate that the type is supported
        if (!in_array(strtolower($type), $this->supportedPaymentTypes)) {
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

    protected function validateData(object $data): ?JsonResponse
    {
        $errors = $this->validator->validate($data);

        if (count($errors) > 0) {
            return $this->json($this->formatValidationErrors($errors), Response::HTTP_BAD_REQUEST);
        }

        return null;
    }

    /**
     * @return array<string, array<int, array<string, string>>>
     */
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
