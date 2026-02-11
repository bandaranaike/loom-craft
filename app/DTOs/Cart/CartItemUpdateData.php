<?php

namespace App\DTOs\Cart;

use App\Http\Requests\Cart\UpdateCartItemRequest;
use App\Models\User;

class CartItemUpdateData
{
    public function __construct(
        public ?User $user,
        public ?string $guestToken,
        public int $cartItemId,
        public int $quantity,
    ) {}

    public static function fromRequest(UpdateCartItemRequest $request, int $cartItemId): self
    {
        return new self(
            $request->user(),
            $request->cookie('loomcraft_guest_token'),
            $cartItemId,
            $request->integer('quantity'),
        );
    }
}
