<?php

namespace App\DTOs\Product;

class ProductPublicListItem
{
    public function __construct(
        public int $id,
        public string $slug,
        public string $name,
        public string $originalPrice,
        public string $sellingPrice,
        public string $effectiveDiscountPercentage,
        public bool $hasDiscount,
        public string $vendorName,
        public ?string $vendorSlug,
        public ?string $vendorLocation,
        public ?string $imageUrl,
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
            'id' => $this->id,
            'slug' => $this->slug,
            'name' => $this->name,
            'original_price' => $this->originalPrice,
            'selling_price' => $this->sellingPrice,
            'effective_discount_percentage' => $this->effectiveDiscountPercentage,
            'has_discount' => $this->hasDiscount,
            'vendor_name' => $this->vendorName,
            'vendor_slug' => $this->vendorSlug,
            'vendor_location' => $this->vendorLocation,
            'image_url' => $this->imageUrl,
            'categories' => $this->categories,
            'colors' => $this->colors,
        ];
    }
}
