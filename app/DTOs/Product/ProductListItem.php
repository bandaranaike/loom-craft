<?php

namespace App\DTOs\Product;

class ProductListItem
{
    public function __construct(
        public int $id,
        public string $name,
        public string $status,
        public string $vendorPrice,
        public string $sellingPrice,
        public ?string $submittedAt,
    ) {}

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'status' => $this->status,
            'vendor_price' => $this->vendorPrice,
            'selling_price' => $this->sellingPrice,
            'submitted_at' => $this->submittedAt,
        ];
    }
}
