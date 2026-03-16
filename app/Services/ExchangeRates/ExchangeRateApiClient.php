<?php

namespace App\Services\ExchangeRates;

use Carbon\CarbonImmutable;
use Illuminate\Http\Client\PendingRequest;
use Illuminate\Support\Facades\Http;
use RuntimeException;

class ExchangeRateApiClient
{
    /**
     * @return array{base_currency: string, rates: array<string, float>, fetched_at: CarbonImmutable, source: string}
     */
    public function latest(string $baseCurrency): array
    {
        $response = $this->request()
            ->get("/v6/latest/{$baseCurrency}")
            ->throw()
            ->json();

        $rates = $response['rates'] ?? null;
        $fetchedAt = $response['time_last_update_unix'] ?? null;

        if (! is_array($rates)) {
            throw new RuntimeException('Exchange-rate provider response is invalid.');
        }

        return [
            'base_currency' => strtoupper($baseCurrency),
            'rates' => array_map(
                static fn (mixed $rate): float => (float) $rate,
                array_filter($rates, static fn (mixed $rate): bool => is_numeric($rate)),
            ),
            'fetched_at' => is_numeric($fetchedAt)
                ? CarbonImmutable::createFromTimestamp((int) $fetchedAt)
                : CarbonImmutable::now(),
            'source' => (string) config('commerce.exchange_rate_source', 'open_er_api'),
        ];
    }

    private function request(): PendingRequest
    {
        return Http::baseUrl((string) config('commerce.exchange_rate_base_url'))
            ->acceptJson()
            ->timeout((int) config('commerce.exchange_rate_timeout_seconds', 15));
    }
}
