<?php

namespace App\Service\Gateway;

use App\Dto\TransactionRequestDto;
use App\Dto\TransactionResponseDto;
use App\Service\PaymentHandlerInterface;

class AciHandler implements PaymentHandlerInterface
{
    public function processTransaction(TransactionRequestDto $transactionData): TransactionResponseDto
    {
        return [
            'status' => 'success',
            'message' => 'Transaction processed via ACI',
        ];
    }
}
