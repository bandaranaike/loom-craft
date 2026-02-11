<?php

namespace App\DTOs\Cart;

class CartShowResult
{
    public function __construct(
        public CartSummaryResult $cart,
        public ?string $guestToken,
    ) {}

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'cart' => $this->cart->toArray(),
        ];
    }
}
