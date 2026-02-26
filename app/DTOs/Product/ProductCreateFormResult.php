<?php

namespace App\DTOs\Product;

class ProductCreateFormResult
{
    public function __construct(
        public string $commissionRate,
        public ?string $vendorName,
        public ?string $vendorSlug,
    ) {}

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'commission_rate' => $this->commissionRate,
            'vendor_name' => $this->vendorName,
            'vendor_slug' => $this->vendorSlug,
        ];
    }
}
