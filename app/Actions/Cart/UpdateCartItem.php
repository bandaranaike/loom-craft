<?php

namespace App\Actions\Cart;

use App\DTOs\Cart\CartItemUpdateData;
use App\DTOs\Cart\CartMutationResult;
use App\Models\Cart;
use App\Models\CartItem;
use App\Models\Product;
use App\ValueObjects\Money;
use Illuminate\Support\Facades\Gate;

class UpdateCartItem
{
    public function handle(CartItemUpdateData $data): CartMutationResult
    {
        Gate::authorize('access', Cart::class);

        $cartItem = CartItem::query()->with(['cart', 'product'])->findOrFail($data->cartItemId);
        $cart = $cartItem->cart;

        Gate::authorize('manage', [$cart, $data->guestToken]);

        if ($data->quantity <= 0) {
            $cartItem->delete();

            return new CartMutationResult($cart->id, $cart->guest_token);
        }

        $product = $cartItem->product;

        if (! $product instanceof Product) {
            throw new \RuntimeException('Cart item product is missing.');
        }

        $cartItem->update([
            'quantity' => $data->quantity,
            'unit_price' => Money::fromString((string) $product->selling_price)->amount,
        ]);

        return new CartMutationResult($cart->id, $cart->guest_token);
    }
}
