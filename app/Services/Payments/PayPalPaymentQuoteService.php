<?php

namespace App\Services\Payments;

use App\DTOs\Payment\PayPalPaymentQuote;
use App\Models\ExchangeRate;
use App\ValueObjects\Money;
use Carbon\CarbonImmutable;
use RuntimeException;

class PayPalPaymentQuoteService
{
    public function quote(string $amount): PayPalPaymentQuote
    {
        $exchangeRate = ExchangeRate::query()
            ->where('from_currency', strtoupper((string) config('commerce.base_currency', 'LKR')))
            ->where('to_currency', strtoupper((string) config('commerce.paypal_currency', 'USD')))
            ->orderByDesc('fetched_at')
            ->orderByDesc('id')
            ->first();

        if (! $exchangeRate instanceof ExchangeRate) {
            throw new RuntimeException('PayPal is temporarily unavailable because the exchange rate has not been loaded yet.');
        }

        $fetchedAt = CarbonImmutable::instance($exchangeRate->fetched_at);
        $staleHours = (int) config('commerce.exchange_rate_stale_hours', 24);

        if ($fetchedAt->lt(now()->subHours($staleHours))) {
            throw new RuntimeException('PayPal is temporarily unavailable because the exchange rate is older than 24 hours.');
        }

        $originalAmount = Money::fromString($amount)->amount;
        $convertedAmount = number_format(
            (float) $originalAmount * (float) $exchangeRate->rate,
            2,
            '.',
            '',
        );

        return new PayPalPaymentQuote(
            $originalAmount,
            $exchangeRate->from_currency,
            $convertedAmount,
            $exchangeRate->to_currency,
            number_format((float) $exchangeRate->rate, 8, '.', ''),
            $exchangeRate->source,
            $fetchedAt,
        );
    }
}
