<?php

namespace App\DTOs\Cart;

use App\Http\Requests\Cart\StoreCartItemRequest;
use App\Models\User;
use App\ValueObjects\Currency;

class CartItemStoreData
{
    public function __construct(
        public ?User $user,
        public ?string $guestToken,
        public int $productId,
        public ?int $productVariationId,
        public int $quantity,
        public Currency $currency,
    ) {}

    public static function fromRequest(StoreCartItemRequest $request): self
    {
        return new self(
            $request->user(),
            $request->cookie('loomcraft_guest_token'),
            $request->integer('product_id'),
            $request->filled('product_variation_id') ? $request->integer('product_variation_id') : null,
            $request->integer('quantity'),
            Currency::default(),
        );
    }
}
