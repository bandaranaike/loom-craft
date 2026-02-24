<?php

namespace App\DTOs\Order;

class DashboardOrderHistoriesResult
{
    /**
     * @param  list<OrderSummaryResult>  $orderHistories
     */
    public function __construct(public array $orderHistories) {}

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'order_histories' => array_map(
                static fn (OrderSummaryResult $order): array => $order->toArray(),
                $this->orderHistories,
            ),
        ];
    }
}
