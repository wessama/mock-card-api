<?php

namespace App\Dto;

use DateTime;
use DateTimeInterface;
use Exception;
use Symfony\Component\Serializer\Attribute\Groups;

class TransactionResponseDto
{
    #[Groups(['api_read'])]
    public ?string $timestamp;

    #[Groups(['api_read'])]
    public ?string $transactionId;

    #[Groups(['api_read'])]
    public ?string $bin;

    #[Groups(['api_read'])]
    public float $amount;

    #[Groups(['api_read'])]
    public ?string $currency;

    public function __construct(
        string|int $timestamp,
        string $transactionId,
        string $bin,
        float $amount,
        string $currency
    ) {
        $this->timestamp = $this->parseTimestamp($timestamp);
        $this->transactionId = $transactionId;
        $this->bin = $bin;
        $this->amount = $amount;
        $this->currency = $currency;
    }

    private function parseTimestamp(string|int $timestamp): ?string
    {
        try {
            if (is_numeric($timestamp)) {
                // If the timestamp is numeric, assume it's a Unix timestamp
                $dateTime = (new DateTime())->setTimestamp((int)$timestamp);
            } else {
                // Try to parse other formats (ISO 8601, etc.)
                $dateTime = new DateTime($timestamp);
            }

            // Return the unified format (e.g., ISO 8601)
            return $dateTime->format(DateTimeInterface::ATOM);
        } catch (Exception $e) {
            // Handle parsing error, return null or a default value if needed
            return null;
        }
    }
}
