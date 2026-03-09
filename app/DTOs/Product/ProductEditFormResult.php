<?php

namespace App\DTOs\Product;

class ProductEditFormResult
{
    /**
     * @param  array{
     *     id: int,
     *     name: string,
     *     description: string,
     *     vendor_price: string,
     *     materials: string|null,
     *     pieces_count: int|null,
     *     production_time_days: int|null,
     *     dimension_length: float|null,
     *     dimension_width: float|null,
     *     dimension_height: float|null,
     *     dimension_unit: string|null,
     *     category_ids: list<int>,
     *     color_ids: list<int>,
     *     images: list<array{id: int, url: string}>
     * }  $product
     */
    public function __construct(
        public string $commissionRate,
        public ?string $vendorName,
        public ?string $vendorSlug,
        public array $product,
        /** @var list<array{id: int, name: string, slug: string}> */
        public array $categories,
        /** @var list<array{id: int, name: string, slug: string}> */
        public array $colors,
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
            'product' => $this->product,
            'categories' => $this->categories,
            'colors' => $this->colors,
        ];
    }
}
