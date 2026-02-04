<?php

namespace App\DTOs\Product;

class ProductCreateFormResult
{
    public function __construct(
        public string $commissionRate,
        public ?string $vendorName,
    ) {}

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'commission_rate' => $this->commissionRate,
            'vendor_name' => $this->vendorName,
        ];
    }
}
