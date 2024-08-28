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

class Shift4Handler implements PaymentHandlerInterface
{
    protected string $cardToken;
    protected string $customerToken;

    public function __construct(
        protected string $apiUrl,
        protected string $apiKey,
        private readonly HttpClientInterface $httpClient,
        private readonly LoggerInterface $logger,
    ) {
    }

    /**
     * For demonstrative purposes, a customer was created in the Shift4 system and a
     * main card was added to the customer's account. In a real-world scenario, the
     * card token would be obtained using the Cards API.
     *
     * @see https://dev.shift4.com/docs/api#card-list
     */
    public function setCardToken(string $cardToken): void
    {
        $this->cardToken = $cardToken;
    }

    /**
     * For demonstrative purposes, a customer was created in the Shift4 system.
     *
     * @see https://dev.shift4.com/customers
     */
    public function setCustomerToken(string $customerToken): void
    {
        $this->customerToken = $customerToken;
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
            'amount' => $transactionData->amount * 100,
            'currency' => $transactionData->currency,
            'customerId' => $this->customerToken,
            'card' => $this->cardToken,
            'description' => 'Example charge',
        ];

        try {
            // Make the HTTP request
            $response = $this->httpClient->request('POST', "{$this->apiUrl}/charges", [
                'auth_basic' => [$this->apiKey, ''], // Basic Auth with an empty password
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
            timestamp: $data['created'] ?? null,
            transactionId: $data['id'] ?? null,
            bin: $data['card']['first6'] ?? null,
            amount: $data['amount'] / 100,
            currency: $data['currency'] ?? null
        );
    }
}
