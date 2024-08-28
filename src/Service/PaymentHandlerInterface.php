<?php

namespace App\Service;

use App\Dto\TransactionRequestDto;
use App\Dto\TransactionResponseDto;

interface PaymentHandlerInterface
{
    public function processTransaction(TransactionRequestDto $transactionData): TransactionResponseDto;
}
