<?php

namespace App\DTOs\Order;

class AdminOrderListItem
{
    public function __construct(
        public int $id,
        public string $status,
        public string $currency,
        public string $total,
        public int $itemCount,
        public ?string $placedAt,
        public string $paymentMethod,
        public string $paymentStatus,
    ) {}

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'status' => $this->status,
            'currency' => $this->currency,
            'total' => $this->total,
            'item_count' => $this->itemCount,
            'placed_at' => $this->placedAt,
            'payment_method' => $this->paymentMethod,
            'payment_status' => $this->paymentStatus,
        ];
    }
}
