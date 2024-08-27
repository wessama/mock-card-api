<?php

namespace App\Controller\Api;

use App\Service\PaymentHandlerProvider;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;

class TransactionController extends AbstractController
{
    public function __construct(
        private readonly PaymentHandlerProvider $paymentHandlerProvider
    ) {
    }

    #[Route('/api/transaction/{type}', name: 'api_transaction', methods: ['POST'])]
    public function handleTransactionAction(Request $request, string $type): JsonResponse
    {
        $transactionData = $request->request->all();
        $handler = $this->paymentHandlerProvider->getHandler($type);
        $response = $handler->processTransaction($transactionData);

        return new JsonResponse($response);
    }
}
