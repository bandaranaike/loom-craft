<?php

namespace App\Services\ExchangeRates;

use App\Models\ExchangeRate;
use RuntimeException;

class ExchangeRateSyncService
{
    public function __construct(private ExchangeRateApiClient $client) {}

    public function syncPayPalRate(): ExchangeRate
    {
        $baseCurrency = strtoupper((string) config('commerce.paypal_currency', 'USD'));
        $sourceCurrency = strtoupper((string) config('commerce.base_currency', 'LKR'));
        $payload = $this->client->latest($baseCurrency);
        $quotedSourceRate = $payload['rates'][$sourceCurrency] ?? null;

        if (! is_float($quotedSourceRate) || $quotedSourceRate <= 0) {
            throw new RuntimeException("Exchange-rate provider did not return a valid {$sourceCurrency} rate.");
        }

        $rate = number_format(1 / $quotedSourceRate, 8, '.', '');

        return ExchangeRate::query()->create([
            'from_currency' => $sourceCurrency,
            'to_currency' => $baseCurrency,
            'rate' => $rate,
            'source' => $payload['source'],
            'fetched_at' => $payload['fetched_at'],
        ]);
    }
}
