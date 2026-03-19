<?php

namespace App\Actions\Order;

use App\DTOs\Order\DashboardOrderHistoriesResult;
use App\DTOs\Order\OrderAddressSummary;
use App\DTOs\Order\OrderIndexData;
use App\DTOs\Order\OrderItemSummary;
use App\DTOs\Order\OrderSummaryResult;
use App\Models\Order;
use App\Models\OrderAddress;
use App\Models\OrderItem;
use App\ValueObjects\Money;
use Illuminate\Support\Facades\Gate;

class ListDashboardOrderHistories
{
    public function handle(OrderIndexData $data): DashboardOrderHistoriesResult
    {
        Gate::authorize('viewDashboard', Order::class);

        $orderHistories = Order::query()
            ->where('user_id', $data->user->id)
            ->with(['items.product.vendor', 'addresses', 'payment'])
            ->latest('placed_at')
            ->limit(10)
            ->get()
            ->map(function (Order $order): OrderSummaryResult {
                $items = $order->items->map(
                    function (OrderItem $item): OrderItemSummary {
                        return new OrderItemSummary(
                            $item->id,
                            $item->product?->name ?? 'Unavailable product',
                            $item->product?->vendor?->display_name ?? 'Unavailable vendor',
                            $item->product?->vendor?->slug,
                            $item->quantity,
                            Money::fromString((string) $item->unit_price)->amount,
                            Money::fromString((string) $item->line_total)->amount,
                        );
                    },
                )->all();

                $addresses = $order->addresses->map(
                    fn (OrderAddress $address): OrderAddressSummary => new OrderAddressSummary(
                        $address->type,
                        $address->full_name,
                        $address->line1,
                        $address->line2,
                        $address->city,
                        $address->region,
                        $address->postal_code,
                        $address->country_code,
                        $address->phone,
                    ),
                )->all();

                return new OrderSummaryResult(
                    $order->id,
                    $order->public_id,
                    $order->status,
                    $order->currency,
                    Money::fromString((string) $order->subtotal)->amount,
                    Money::fromString((string) $order->commission_total)->amount,
                    Money::fromString((string) $order->total)->amount,
                    $order->shipping_responsibility,
                    $order->placed_at?->toDateTimeString(),
                    $order->payment?->method ?? 'pending',
                    $order->payment?->status ?? 'pending',
                    $order->payment?->amount,
                    $order->payment?->currency,
                    $order->payment?->original_amount,
                    $order->payment?->original_currency,
                    $items,
                    $addresses,
                    null,
                );
            })
            ->all();

        return new DashboardOrderHistoriesResult($orderHistories);
    }
}
