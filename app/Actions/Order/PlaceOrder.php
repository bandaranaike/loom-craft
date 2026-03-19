<?php

namespace App\Actions\Order;

use App\DTOs\Order\CheckoutStoreData;
use App\DTOs\Order\OrderPlacementResult;
use App\DTOs\Payment\PayPalPaymentQuote;
use App\Models\Cart;
use App\Models\Order;
use App\Models\Product;
use App\ValueObjects\Currency;
use App\ValueObjects\Money;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\ValidationException;

class PlaceOrder
{
    public function handle(
        CheckoutStoreData $data,
        ?string $paymentProviderReference = null,
        ?PayPalPaymentQuote $payPalPaymentQuote = null,
    ): OrderPlacementResult {
        Gate::authorize('create', Order::class);
        $commissionRate = (string) config('commerce.commission_rate');

        $cart = $this->normalizeCurrency($this->resolveCart($data));

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

                $unitPrice = Money::fromString((string) $item->unit_price);
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

            $isInstantPaid = in_array($data->paymentMethod, ['stripe', 'paypal', 'paypal_card'], true);
            $paymentStatus = $isInstantPaid ? 'paid' : 'pending';
            $orderStatus = $isInstantPaid ? 'paid' : 'pending';
            $orderSubtotal = Money::fromString((string) $subtotal)->amount;
            $orderCurrency = $data->currency->code;
            $paymentAmount = $orderSubtotal;
            $paymentCurrency = $orderCurrency;
            $originalAmount = $orderSubtotal;
            $originalCurrency = $orderCurrency;
            $exchangeRate = null;
            $exchangeRateSource = null;
            $exchangeRateFetchedAt = null;

            if (in_array($data->paymentMethod, ['paypal', 'paypal_card'], true)) {
                if ($payPalPaymentQuote === null) {
                    throw ValidationException::withMessages([
                        'payment_method' => 'PayPal conversion details are missing. Please start checkout again.',
                    ]);
                }

                if ($payPalPaymentQuote->originalAmount !== $orderSubtotal || $payPalPaymentQuote->originalCurrency !== $orderCurrency) {
                    throw ValidationException::withMessages([
                        'cart' => 'Your cart changed during PayPal checkout. Please review the latest prices and try again.',
                    ]);
                }

                $paymentAmount = $payPalPaymentQuote->convertedAmount;
                $paymentCurrency = $payPalPaymentQuote->convertedCurrency;
                $originalAmount = $payPalPaymentQuote->originalAmount;
                $originalCurrency = $payPalPaymentQuote->originalCurrency;
                $exchangeRate = $payPalPaymentQuote->exchangeRate;
                $exchangeRateSource = $payPalPaymentQuote->source;
                $exchangeRateFetchedAt = $payPalPaymentQuote->fetchedAt;
            }

            $order = Order::query()->create([
                'user_id' => $data->user?->id,
                'guest_name' => $data->user ? null : $data->guestName,
                'guest_email' => $data->user ? null : $data->guestEmail,
                'status' => $orderStatus,
                'currency' => $orderCurrency,
                'subtotal' => $orderSubtotal,
                'commission_total' => Money::fromString((string) $commissionTotal)->amount,
                'total' => $orderSubtotal,
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
                'amount' => $paymentAmount,
                'currency' => $paymentCurrency,
                'original_amount' => $originalAmount,
                'original_currency' => $originalCurrency,
                'exchange_rate' => $exchangeRate,
                'exchange_rate_source' => $exchangeRateSource,
                'exchange_rate_fetched_at' => $exchangeRateFetchedAt,
                'provider_reference' => $paymentProviderReference,
            ]);

            $cart->items()->delete();

            return new OrderPlacementResult(
                $order->id,
                $order->public_id,
                $cart->guest_token,
            );
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

    private function normalizeCurrency(Cart $cart): Cart
    {
        $currency = Currency::default()->code;

        if ($cart->currency !== $currency) {
            $cart->update(['currency' => $currency]);
        }

        return $cart->refresh();
    }
}
