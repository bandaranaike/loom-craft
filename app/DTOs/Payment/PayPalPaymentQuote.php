<?php

namespace App\DTOs\Payment;

use Carbon\CarbonImmutable;

class PayPalPaymentQuote
{
    public function __construct(
        public string $originalAmount,
        public string $originalCurrency,
        public string $convertedAmount,
        public string $convertedCurrency,
        public string $exchangeRate,
        public string $source,
        public CarbonImmutable $fetchedAt,
    ) {}

    /**
     * @return array<string, string>
     */
    public function toArray(): array
    {
        return [
            'original_amount' => $this->originalAmount,
            'original_currency' => $this->originalCurrency,
            'converted_amount' => $this->convertedAmount,
            'converted_currency' => $this->convertedCurrency,
            'exchange_rate' => $this->exchangeRate,
            'source' => $this->source,
            'fetched_at' => $this->fetchedAt->toIso8601String(),
        ];
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public static function fromArray(array $data): self
    {
        return new self(
            self::requiredString($data, 'original_amount'),
            self::requiredString($data, 'original_currency'),
            self::requiredString($data, 'converted_amount'),
            self::requiredString($data, 'converted_currency'),
            self::requiredString($data, 'exchange_rate'),
            self::requiredString($data, 'source'),
            CarbonImmutable::parse(self::requiredString($data, 'fetched_at')),
        );
    }

    /**
     * @param  array<string, mixed>  $data
     */
    private static function requiredString(array $data, string $key): string
    {
        $value = $data[$key] ?? null;

        return is_string($value) ? $value : '';
    }
}
