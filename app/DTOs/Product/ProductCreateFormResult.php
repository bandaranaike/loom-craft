<?php

namespace App\DTOs\Product;

class ProductCreateFormResult
{
    public function __construct(
        public string $commissionRate,
        public ?string $vendorName,
        public ?string $vendorSlug,
        /** @var list<array{id: int, name: string, slug: string}> */
        public array $categories,
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
            'categories' => $this->categories,
        ];
    }
}
