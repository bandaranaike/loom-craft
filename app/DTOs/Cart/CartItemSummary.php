<?php

namespace App\DTOs\Cart;

class CartItemSummary
{
    public function __construct(
        public int $id,
        public int $productId,
        public string $name,
        public string $vendorName,
        public ?string $imageUrl,
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
            'product_id' => $this->productId,
            'name' => $this->name,
            'vendor_name' => $this->vendorName,
            'image_url' => $this->imageUrl,
            'quantity' => $this->quantity,
            'unit_price' => $this->unitPrice,
            'line_total' => $this->lineTotal,
        ];
    }
}
