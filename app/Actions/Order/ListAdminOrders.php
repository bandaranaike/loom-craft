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

        $query = Order::query()
            ->withCount('items')
            ->with(['payment', 'user'])
            ->latest('placed_at');

        if ($data->status !== null) {
            $query->where('status', $data->status);
        }

        $orders = $query
            ->get()
            ->map(function (Order $order): AdminOrderListItem {
                $payment = $order->payment;

                if ($payment === null) {
                    throw new \RuntimeException('Order payment is missing.');
                }

                return new AdminOrderListItem(
                    $order->id,
                    $order->public_id,
                    $order->status,
                    $order->currency,
                    Money::fromString((string) $order->total)->amount,
                    (int) $order->items_count,
                    $order->placed_at?->toDateTimeString(),
                    $payment->method,
                    $payment->status,
                    $order->user?->name ?? $order->guest_name,
                );
            })
            ->all();

        return new AdminOrderListResult(
            $orders,
            $data->status,
            OrderIndexData::ORDER_STATUSES,
        );
    }
}
