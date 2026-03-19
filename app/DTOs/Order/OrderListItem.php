<?php

namespace App\DTOs\Order;

class OrderListItem
{
    public function __construct(
        public int $id,
        public ?string $publicId,
        public string $status,
        public string $currency,
        public string $total,
        public int $itemCount,
        public ?string $placedAt,
    ) {}

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'public_id' => $this->publicId,
            'status' => $this->status,
            'currency' => $this->currency,
            'total' => $this->total,
            'item_count' => $this->itemCount,
            'placed_at' => $this->placedAt,
        ];
    }
}
