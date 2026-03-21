<?php

namespace App\DTOs\Order;

class AdminOrderListResult
{
    /**
     * @param  list<AdminOrderListItem>  $orders
     * @param  list<string>  $statusOptions
     */
    public function __construct(
        public array $orders,
        public ?string $selectedStatus,
        public array $statusOptions,
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
            'selected_status' => $this->selectedStatus,
            'status_options' => $this->statusOptions,
        ];
    }
}
