<?php

namespace App\DTOs\Product;

class ProductVendorSummary
{
    public function __construct(
        public int $id,
        public string $displayName,
        public ?string $location,
    ) {}

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'display_name' => $this->displayName,
            'location' => $this->location,
        ];
    }
}
