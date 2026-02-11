<?php

namespace App\Actions\Cart;

use App\DTOs\Cart\CartItemRemoveData;
use App\DTOs\Cart\CartMutationResult;
use App\Models\Cart;
use App\Models\CartItem;
use Illuminate\Support\Facades\Gate;

class RemoveCartItem
{
    public function handle(CartItemRemoveData $data): CartMutationResult
    {
        Gate::authorize('access', Cart::class);

        $cartItem = CartItem::query()->with('cart')->findOrFail($data->cartItemId);
        $cart = $cartItem->cart;

        Gate::authorize('manage', [$cart, $data->guestToken]);

        $cartItem->delete();

        return new CartMutationResult($cart->id, $cart->guest_token);
    }
}
