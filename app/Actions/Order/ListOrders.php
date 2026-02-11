<?php

namespace App\Actions\Order;

use App\DTOs\Order\OrderIndexData;
use App\DTOs\Order\OrderListItem;
use App\DTOs\Order\OrderListResult;
use App\Models\Order;
use App\ValueObjects\Money;
use Illuminate\Support\Facades\Gate;

class ListOrders
{
    public function handle(OrderIndexData $data): OrderListResult
    {
        Gate::authorize('viewOwn', Order::class);

        $orders = Order::query()
            ->where('user_id', $data->user->id)
            ->withCount('items')
            ->latest('placed_at')
            ->get()
            ->map(fn (Order $order): OrderListItem => new OrderListItem(
                $order->id,
                $order->status,
                $order->currency,
                Money::fromString((string) $order->total)->amount,
                (int) $order->items_count,
                $order->placed_at?->toDateTimeString(),
            ))
            ->all();

        return new OrderListResult($orders);
    }
}
