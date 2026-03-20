<?php

namespace App\Actions\Order;

use App\DTOs\Order\OrderIndexData;
use App\DTOs\Order\VendorOrderListItem;
use App\DTOs\Order\VendorOrderListResult;
use App\Models\Order;
use App\ValueObjects\Money;
use Illuminate\Support\Facades\Gate;

class ListVendorOrders
{
    public function handle(OrderIndexData $data): VendorOrderListResult
    {
        Gate::authorize('viewVendorIndex', Order::class);

        $vendor = $data->user->vendor;

        if ($vendor === null) {
            throw new \RuntimeException('Vendor profile is required to view orders.');
        }

        $orders = Order::query()
            ->whereHas('items', fn ($query) => $query->where('vendor_id', $vendor->id))
            ->with(['payment'])
            ->withCount('items')
            ->withCount([
                'items as vendor_items_count' => fn ($query) => $query->where('vendor_id', $vendor->id),
            ])
            ->latest('placed_at')
            ->get()
            ->map(function (Order $order): VendorOrderListItem {
                $payment = $order->payment;

                if ($payment === null) {
                    throw new \RuntimeException('Order payment is missing.');
                }

                return new VendorOrderListItem(
                    $order->id,
                    $order->public_id,
                    $order->status,
                    $order->currency,
                    Money::fromString((string) $order->total)->amount,
                    (int) $order->items_count,
                    (int) $order->vendor_items_count,
                    $order->placed_at?->toDateTimeString(),
                    $payment->method,
                    $payment->status,
                );
            })
            ->all();

        return new VendorOrderListResult($orders);
    }
}
