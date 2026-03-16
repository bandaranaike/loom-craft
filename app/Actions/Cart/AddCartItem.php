<?php

namespace App\Actions\Cart;

use App\DTOs\Cart\CartItemStoreData;
use App\DTOs\Cart\CartMutationResult;
use App\Models\Cart;
use App\Models\Product;
use App\Services\ProductPricingService;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class AddCartItem
{
    public function __construct(private ProductPricingService $productPricingService) {}

    public function handle(CartItemStoreData $data): CartMutationResult
    {
        Gate::authorize('access', Cart::class);

        [$cart, $guestToken] = $this->resolveCart($data);

        Gate::authorize('manage', [$cart, $guestToken]);

        if ($cart->currency !== $data->currency->code) {
            throw ValidationException::withMessages([
                'currency' => 'Cart currency must remain consistent for all items.',
            ]);
        }

        $product = Product::query()->with(['vendor', 'categories'])->findOrFail($data->productId);

        if ($product->status !== 'active' || $product->vendor?->status !== 'approved') {
            throw ValidationException::withMessages([
                'product_id' => 'This product is not available for purchase.',
            ]);
        }

        $unitPrice = $this->productPricingService->forProduct($product)->discountedPrice;

        $existing = $cart->items()->where('product_id', $product->id)->first();

        if ($existing) {
            $existing->update([
                'quantity' => $existing->quantity + $data->quantity,
                'unit_price' => $unitPrice,
            ]);
        } else {
            $cart->items()->create([
                'product_id' => $product->id,
                'quantity' => $data->quantity,
                'unit_price' => $unitPrice,
            ]);
        }

        return new CartMutationResult($cart->id, $guestToken);
    }

    /**
     * @return array{0: Cart, 1: string|null}
     */
    private function resolveCart(CartItemStoreData $data): array
    {
        if ($data->user !== null) {
            $cart = Cart::query()->firstOrCreate(
                ['user_id' => $data->user->id],
                ['currency' => $data->currency->code],
            );

            return [$this->normalizeCurrency($cart, $data->currency->code), null];
        }

        $cart = null;

        if ($data->guestToken !== null) {
            $cart = Cart::query()->where('guest_token', $data->guestToken)->first();
        }

        if ($cart === null) {
            $token = (string) Str::uuid();
            $cart = Cart::query()->create([
                'guest_token' => $token,
                'currency' => $data->currency->code,
            ]);

            return [$cart, $token];
        }

        return [$this->normalizeCurrency($cart, $data->currency->code), $cart->guest_token];
    }

    private function normalizeCurrency(Cart $cart, string $currency): Cart
    {
        if ($cart->currency !== $currency) {
            $cart->update(['currency' => $currency]);
        }

        return $cart->refresh();
    }
}
