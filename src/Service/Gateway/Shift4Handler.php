<?php

namespace App\Service\Gateway;

use App\Service\PaymentHandlerInterface;

class Shift4Handler implements PaymentHandlerInterface
{
    public function processTransaction(array $transactionData): array
    {
        return [
            'status' => 'success',
            'message' => 'Transaction processed via Shift4',
        ];
    }
}
