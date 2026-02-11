<?php

namespace App\DTOs\Cart;

use App\Models\User;
use Illuminate\Http\Request;

class CartItemRemoveData
{
    public function __construct(
        public ?User $user,
        public ?string $guestToken,
        public int $cartItemId,
    ) {}

    public static function fromRequest(Request $request, int $cartItemId): self
    {
        return new self(
            $request->user(),
            $request->cookie('loomcraft_guest_token'),
            $cartItemId,
        );
    }
}
