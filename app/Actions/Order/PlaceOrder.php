<?php

namespace App\Actions\Order;

use App\DTOs\Order\CheckoutStoreData;
use App\DTOs\Order\OrderPlacementResult;
use App\Models\Cart;
use App\Models\Order;
use App\Models\Product;
use App\Services\ProductPricingService;
use App\ValueObjects\Money;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\ValidationException;

class PlaceOrder
{
    public function __construct(private ProductPricingService $productPricingService) {}

    public function handle(
        CheckoutStoreData $data,
        ?string $paymentProviderReference = null,
    ): OrderPlacementResult {
        Gate::authorize('create', Order::class);
        $commissionRate = (string) config('commerce.commission_rate');

        $cart = $this->resolveCart($data);

        Gate::authorize('manage', [$cart, $data->guestToken]);

        if ($cart->currency !== $data->currency->code) {
            throw ValidationException::withMessages([
                'currency' => 'Cart currency must match checkout currency.',
            ]);
        }

        $cart->load('items.product.vendor', 'items.product.categories');

        if ($cart->items->isEmpty()) {
            throw ValidationException::withMessages([
                'cart' => 'Your cart is empty.',
            ]);
        }

        return DB::transaction(function () use ($cart, $data, $paymentProviderReference, $commissionRate): OrderPlacementResult {
            $lineItems = [];
            $subtotal = 0.0;
            $commissionTotal = 0.0;

            foreach ($cart->items as $item) {
                $product = $item->product;

                if (! $product instanceof Product) {
                    throw new \RuntimeException('Cart item product is missing.');
                }

                if ($product->status !== 'active' || $product->vendor?->status !== 'approved') {
                    throw ValidationException::withMessages([
                        'cart' => 'One or more items are no longer available.',
                    ]);
                }

                $pricing = $this->productPricingService->forProduct($product);
                $unitPrice = Money::fromString($pricing->discountedPrice);
                $lineTotal = $unitPrice->multiply($item->quantity);
                $commissionAmount = $lineTotal->percentageOf($commissionRate);

                $lineItems[] = [
                    'product_id' => $product->id,
                    'vendor_id' => $product->vendor_id,
                    'quantity' => $item->quantity,
                    'unit_price' => $unitPrice->amount,
                    'commission_rate' => $commissionRate,
                    'commission_amount' => $commissionAmount->amount,
                    'line_total' => $lineTotal->amount,
                ];

                $subtotal += (float) $lineTotal->amount;
                $commissionTotal += (float) $commissionAmount->amount;
            }

            $isInstantPaid = in_array($data->paymentMethod, ['stripe', 'paypal'], true);
            $paymentStatus = $isInstantPaid ? 'paid' : 'pending';
            $orderStatus = $isInstantPaid ? 'paid' : 'pending';

            $order = Order::query()->create([
                'user_id' => $data->user?->id,
                'guest_name' => $data->user ? null : $data->guestName,
                'guest_email' => $data->user ? null : $data->guestEmail,
                'status' => $orderStatus,
                'currency' => $data->currency->code,
                'subtotal' => Money::fromString((string) $subtotal)->amount,
                'commission_total' => Money::fromString((string) $commissionTotal)->amount,
                'total' => Money::fromString((string) $subtotal)->amount,
                'shipping_responsibility' => $data->shippingResponsibility,
                'placed_at' => now(),
            ]);

            $order->items()->createMany($lineItems);
            $order->addresses()->createMany([
                $data->shippingAddress->toArray('shipping'),
                $data->billingAddress->toArray('billing'),
            ]);

            $order->payment()->create([
                'method' => $data->paymentMethod,
                'status' => $paymentStatus,
                'amount' => Money::fromString((string) $subtotal)->amount,
                'currency' => $data->currency->code,
                'provider_reference' => $paymentProviderReference,
            ]);

            $cart->items()->delete();

            return new OrderPlacementResult($order->id, $cart->guest_token);
        });
    }

    private function resolveCart(CheckoutStoreData $data): Cart
    {
        if ($data->user !== null) {
            $cart = Cart::query()->where('user_id', $data->user->id)->first();

            if ($cart === null) {
                throw ValidationException::withMessages([
                    'cart' => 'Unable to locate your cart.',
                ]);
            }

            return $cart;
        }

        if ($data->guestToken === null) {
            throw ValidationException::withMessages([
                'cart' => 'Unable to locate your cart.',
            ]);
        }

        $cart = Cart::query()->where('guest_token', $data->guestToken)->first();

        if ($cart === null) {
            throw ValidationException::withMessages([
                'cart' => 'Unable to locate your cart.',
            ]);
        }

        return $cart;
    }
}
