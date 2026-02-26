<?php

namespace App\DTOs\Order;

class OrderItemSummary
{
    public function __construct(
        public int $id,
        public string $productName,
        public string $vendorName,
        public ?string $vendorSlug,
        public int $quantity,
        public string $unitPrice,
        public string $lineTotal,
    ) {}

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'product_name' => $this->productName,
            'vendor_name' => $this->vendorName,
            'vendor_slug' => $this->vendorSlug,
            'quantity' => $this->quantity,
            'unit_price' => $this->unitPrice,
            'line_total' => $this->lineTotal,
        ];
    }
}
