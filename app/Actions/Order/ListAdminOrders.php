<?php

namespace App\Actions\Order;

use App\DTOs\Order\AdminOrderListItem;
use App\DTOs\Order\AdminOrderListResult;
use App\DTOs\Order\OrderIndexData;
use App\Models\Order;
use App\ValueObjects\Money;
use Illuminate\Support\Facades\Gate;

class ListAdminOrders
{
    public function handle(OrderIndexData $data): AdminOrderListResult
    {
        Gate::authorize('viewAny', Order::class);

        $orders = Order::query()
            ->withCount('items')
            ->with('payment')
            ->latest('placed_at')
            ->get()
            ->map(function (Order $order): AdminOrderListItem {
                $payment = $order->payment;

                if ($payment === null) {
                    throw new \RuntimeException('Order payment is missing.');
                }

                return new AdminOrderListItem(
                    $order->id,
                    $order->status,
                    $order->currency,
                    Money::fromString((string) $order->total)->amount,
                    (int) $order->items_count,
                    $order->placed_at?->toDateTimeString(),
                    $payment->method,
                    $payment->status,
                );
            })
            ->all();

        return new AdminOrderListResult($orders);
    }
}
