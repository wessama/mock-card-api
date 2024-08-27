<?php

namespace App\Service;

interface PaymentHandlerInterface
{
    public function processTransaction(array $transactionData): array;
}
