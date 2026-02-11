<?php

namespace App\DTOs\Cart;

class CartMutationResult
{
    public function __construct(
        public int $cartId,
        public ?string $guestToken,
    ) {}
}
