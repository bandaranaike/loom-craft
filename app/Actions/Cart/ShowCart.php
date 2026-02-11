<?php

namespace App\Actions\Cart;

use App\DTOs\Cart\CartItemSummary;
use App\DTOs\Cart\CartSessionData;
use App\DTOs\Cart\CartShowResult;
use App\DTOs\Cart\CartSummaryResult;
use App\Models\Cart;
use App\Models\CartItem;
use App\Models\Product;
use App\ValueObjects\Money;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class ShowCart
{
    public function handle(CartSessionData $data): CartShowResult
    {
        Gate::authorize('access', Cart::class);

        [$cart, $guestToken] = $this->resolveCart($data);

        $cart->load([
            'items.product.vendor',
            'items.product.media' => fn ($query) => $query->orderBy('sort_order'),
        ]);

        $items = $cart->items->map(function (CartItem $item): CartItemSummary {
            $product = $item->product;

            if (! $product instanceof Product) {
                throw new \RuntimeException('Cart item product is missing.');
            }

            $vendor = $product->vendor;

            if ($vendor === null) {
                throw new \RuntimeException('Cart item vendor is missing.');
            }

            $image = $product->media->firstWhere('type', 'image');
            $lineTotal = Money::fromString((string) $item->unit_price)
                ->multiply($item->quantity);

            return new CartItemSummary(
                $item->id,
                $product->id,
                $product->name,
                $vendor->display_name,
                $image ? Storage::disk('public')->url($image->path) : null,
                $item->quantity,
                Money::fromString((string) $item->unit_price)->amount,
                $lineTotal->amount,
            );
        })->all();

        $subtotalValue = array_reduce(
            $items,
            static fn (float $carry, CartItemSummary $item): float => $carry + (float) $item->lineTotal,
            0.0,
        );

        $summary = new CartSummaryResult(
            $cart->id,
            $cart->currency,
            $items,
            count($items),
            Money::fromString((string) $subtotalValue)->amount,
        );

        return new CartShowResult($summary, $guestToken);
    }

    /**
     * @return array{0: Cart, 1: string|null}
     */
    private function resolveCart(CartSessionData $data): array
    {
        if ($data->user !== null) {
            return [$this->resolveUserCart($data), null];
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

        return [$cart, $cart->guest_token];
    }

    private function resolveUserCart(CartSessionData $data): Cart
    {
        $user = $data->user;

        if ($user === null) {
            throw new \RuntimeException('User is required to resolve a user cart.');
        }

        return DB::transaction(function () use ($data, $user): Cart {
            $cart = Cart::query()->firstOrCreate(
                ['user_id' => $user->id],
                ['currency' => $data->currency->code],
            );

            if ($data->guestToken === null) {
                return $cart;
            }

            $guestCart = Cart::query()->where('guest_token', $data->guestToken)->first();

            if ($guestCart === null || $guestCart->id === $cart->id) {
                return $cart;
            }

            if ($guestCart->currency !== $cart->currency) {
                return $cart;
            }

            $guestCart->load('items.product');

            foreach ($guestCart->items as $item) {
                $product = $item->product;

                if (! $product instanceof Product) {
                    continue;
                }

                $unitPrice = Money::fromString((string) $product->selling_price)->amount;
                $existing = $cart->items()->where('product_id', $product->id)->first();

                if ($existing) {
                    $existing->update([
                        'quantity' => $existing->quantity + $item->quantity,
                        'unit_price' => $unitPrice,
                    ]);

                    continue;
                }

                $cart->items()->create([
                    'product_id' => $product->id,
                    'quantity' => $item->quantity,
                    'unit_price' => $unitPrice,
                ]);
            }

            $guestCart->items()->delete();
            $guestCart->delete();

            return $cart;
        });
    }
}
