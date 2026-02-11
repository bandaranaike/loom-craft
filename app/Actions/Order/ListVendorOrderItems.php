<?php

namespace App\Actions\Order;

use App\DTOs\Order\OrderIndexData;
use App\DTOs\Order\VendorOrderItemsResult;
use App\DTOs\Order\VendorOrderItemSummary;
use App\Models\Order;
use App\Models\OrderItem;
use App\ValueObjects\Money;
use Illuminate\Support\Facades\Gate;

class ListVendorOrderItems
{
    public function handle(OrderIndexData $data): VendorOrderItemsResult
    {
        Gate::authorize('viewVendorIndex', Order::class);

        $vendor = $data->user->vendor;

        if ($vendor === null) {
            throw new \RuntimeException('Vendor profile is required to view orders.');
        }

        $items = OrderItem::query()
            ->with(['order', 'product'])
            ->where('vendor_id', $vendor->id)
            ->latest()
            ->get()
            ->map(function (OrderItem $item): VendorOrderItemSummary {
                $order = $item->order;
                $product = $item->product;

                if ($order === null || $product === null) {
                    throw new \RuntimeException('Order item references missing order or product.');
                }

                return new VendorOrderItemSummary(
                    $order->id,
                    $item->id,
                    $order->status,
                    $order->currency,
                    $product->name,
                    $item->quantity,
                    Money::fromString((string) $item->line_total)->amount,
                    $order->shipping_responsibility,
                    $order->placed_at?->toDateTimeString(),
                );
            })
            ->all();

        return new VendorOrderItemsResult($items);
    }
}
