<?php

namespace App\Services\Payments;

use App\DTOs\Order\CheckoutViewResult;
use App\Models\User;
use App\ValueObjects\Money;
use Laravel\Cashier\Cashier;
use Laravel\Cashier\Checkout;
use Stripe\Checkout\Session;

class StripeCheckoutService
{
    public function isConfigured(): bool
    {
        return filled(config('cashier.key')) && filled(config('cashier.secret'));
    }

    public function createCheckoutSession(
        ?User $user,
        CheckoutViewResult $checkoutResult,
        string $successUrl,
        string $cancelUrl,
        ?string $guestEmail = null,
    ): Session {
        $builder = $user !== null
            ? Checkout::customer($user)
            : Checkout::guest();

        $checkout = $builder->create(
            $this->lineItems($checkoutResult),
            array_filter([
                'success_url' => $successUrl,
                'cancel_url' => $cancelUrl,
                'customer_email' => $user === null ? $guestEmail : null,
                'metadata' => [
                    'cart_id' => (string) $checkoutResult->cart->cartId,
                    'currency' => strtoupper($checkoutResult->currency),
                ],
            ], static fn (mixed $value): bool => $value !== null),
        );

        return $checkout->asStripeCheckoutSession();
    }

    public function retrieveCheckoutSession(string $sessionId): Session
    {
        return Cashier::stripe()->checkout->sessions->retrieve($sessionId);
    }

    public function normalizeAmountTotal(int $amountTotal): string
    {
        return Money::fromString((string) ($amountTotal / 100))->amount;
    }

    /**
     * @return list<array<string, mixed>>
     */
    private function lineItems(CheckoutViewResult $checkoutResult): array
    {
        return array_map(function ($item) use ($checkoutResult): array {
            return [
                'price_data' => [
                    'currency' => strtolower($checkoutResult->currency),
                    'product_data' => [
                        'name' => $item->name,
                    ],
                    'unit_amount' => $this->toMinorUnits($item->unitPrice),
                ],
                'quantity' => $item->quantity,
            ];
        }, $checkoutResult->cart->items);
    }

    private function toMinorUnits(string $amount): int
    {
        return (int) round(((float) $amount) * 100);
    }
}
