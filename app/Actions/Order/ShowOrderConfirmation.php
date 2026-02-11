<?php

namespace App\Actions\Order;

use App\DTOs\Order\OrderAddressSummary;
use App\DTOs\Order\OrderConfirmationData;
use App\DTOs\Order\OrderItemSummary;
use App\DTOs\Order\OrderSummaryResult;
use App\Models\Order;
use App\Models\OrderAddress;
use App\Models\OrderItem;
use App\ValueObjects\Money;
use Illuminate\Support\Facades\Gate;

class ShowOrderConfirmation
{
    public function handle(OrderConfirmationData $data): OrderSummaryResult
    {
        Gate::authorize('viewConfirmation', Order::class);

        $order = Order::query()
            ->with(['items.product.vendor', 'addresses', 'payment'])
            ->findOrFail($data->orderId);

        if ($data->user) {
            Gate::authorize('view', $order);
        } else {
            Gate::authorize('viewGuest', $order);

            if ($data->guestOrderId !== $order->id) {
                abort(403);
            }
        }

        $items = $order->items->map(function (OrderItem $item): OrderItemSummary {
            $product = $item->product;
            $vendor = $product?->vendor;

            if ($product === null || $vendor === null) {
                throw new \RuntimeException('Order item references missing product or vendor.');
            }

            return new OrderItemSummary(
                $item->id,
                $product->name,
                $vendor->display_name,
                $item->quantity,
                Money::fromString((string) $item->unit_price)->amount,
                Money::fromString((string) $item->line_total)->amount,
            );
        })->all();

        $addresses = $order->addresses->map(fn (OrderAddress $address): OrderAddressSummary => new OrderAddressSummary(
            $address->type,
            $address->full_name,
            $address->line1,
            $address->line2,
            $address->city,
            $address->region,
            $address->postal_code,
            $address->country_code,
            $address->phone,
        ))->all();

        $payment = $order->payment;

        if ($payment === null) {
            throw new \RuntimeException('Order payment is missing.');
        }

        return new OrderSummaryResult(
            $order->id,
            $order->status,
            $order->currency,
            Money::fromString((string) $order->subtotal)->amount,
            Money::fromString((string) $order->commission_total)->amount,
            Money::fromString((string) $order->total)->amount,
            $order->shipping_responsibility,
            $order->placed_at?->toDateTimeString(),
            $payment->method,
            $payment->status,
            $items,
            $addresses,
        );
    }
}
