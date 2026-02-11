<?php

namespace App\DTOs\Order;

use App\DTOs\Cart\CartSummaryResult;

class CheckoutViewResult
{
    /**
     * @param  list<string>  $paymentMethods
     * @param  list<string>  $shippingResponsibilities
     */
    public function __construct(
        public CartSummaryResult $cart,
        public string $currency,
        public array $paymentMethods,
        public array $shippingResponsibilities,
        public ?string $guestName,
        public ?string $guestEmail,
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
            'shipping_responsibilities' => $this->shippingResponsibilities,
            'guest_name' => $this->guestName,
            'guest_email' => $this->guestEmail,
        ];
    }
}
