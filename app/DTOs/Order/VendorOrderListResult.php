<?php

namespace App\DTOs\Order;

class VendorOrderListResult
{
    /**
     * @param  list<VendorOrderListItem>  $orders
     */
    public function __construct(
        public array $orders,
    ) {}

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'orders' => array_map(
                static fn (VendorOrderListItem $order): array => $order->toArray(),
                $this->orders,
            ),
        ];
    }
}
