<?php

namespace App\DTOs\Order;

class OrderListResult
{
    /**
     * @param  list<OrderListItem>  $orders
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
                static fn (OrderListItem $order): array => $order->toArray(),
                $this->orders,
            ),
        ];
    }
}
