<?php

namespace App\DTOs\Cart;

class CartItemSummary
{
    public function __construct(
        public int $id,
        public int $productId,
        public string $name,
        public string $vendorName,
        public ?string $vendorSlug,
        public ?string $imageUrl,
        public int $quantity,
        public string $originalUnitPrice,
        public string $unitPrice,
        public string $originalLineTotal,
        public string $lineTotal,
        public string $effectiveDiscountPercentage,
        public bool $hasDiscount,
        public ?int $availableQuantity,
        public ?int $productionTimeDays,
        public bool $exceedsAvailableStock,
        public ?string $stockDelayMessage,
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
            'vendor_slug' => $this->vendorSlug,
            'image_url' => $this->imageUrl,
            'quantity' => $this->quantity,
            'original_unit_price' => $this->originalUnitPrice,
            'unit_price' => $this->unitPrice,
            'original_line_total' => $this->originalLineTotal,
            'line_total' => $this->lineTotal,
            'effective_discount_percentage' => $this->effectiveDiscountPercentage,
            'has_discount' => $this->hasDiscount,
            'available_quantity' => $this->availableQuantity,
            'production_time_days' => $this->productionTimeDays,
            'exceeds_available_stock' => $this->exceedsAvailableStock,
            'stock_delay_message' => $this->stockDelayMessage,
        ];
    }
}
