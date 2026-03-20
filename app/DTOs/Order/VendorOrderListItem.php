<?php

namespace App\DTOs\Order;

class VendorOrderListItem
{
    public function __construct(
        public int $id,
        public ?string $publicId,
        public string $status,
        public string $currency,
        public string $total,
        public int $itemCount,
        public int $vendorItemCount,
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
            'public_id' => $this->publicId,
            'status' => $this->status,
            'currency' => $this->currency,
            'total' => $this->total,
            'item_count' => $this->itemCount,
            'vendor_item_count' => $this->vendorItemCount,
            'placed_at' => $this->placedAt,
            'payment_method' => $this->paymentMethod,
            'payment_status' => $this->paymentStatus,
        ];
    }
}
