<?php

namespace App\DTOs\Order;

use App\DTOs\Cart\CartSummaryResult;

class CheckoutViewResult
{
    /**
     * @param  list<string>  $paymentMethods
     */
    public function __construct(
        public CartSummaryResult $cart,
        public string $currency,
        public array $paymentMethods,
        public ?string $guestName,
        public ?string $guestEmail,
        public string $defaultCountryCode,
        public ?string $guestToken,
    ) {}

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'cart' => $this->cart->toArray(),
            'currency' => $this->currency,
            'payment_methods' => $this->paymentMethods,
            'guest_name' => $this->guestName,
            'guest_email' => $this->guestEmail,
            'default_country_code' => $this->defaultCountryCode,
        ];
    }
}
