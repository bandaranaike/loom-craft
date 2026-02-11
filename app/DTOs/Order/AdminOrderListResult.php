<?php

namespace App\DTOs\Order;

class AdminOrderListResult
{
    /**
     * @param  list<AdminOrderListItem>  $orders
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
                static fn (AdminOrderListItem $order): array => $order->toArray(),
                $this->orders,
            ),
        ];
    }
}
