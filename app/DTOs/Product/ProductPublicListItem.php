<?php

namespace App\DTOs\Product;

class ProductPublicListItem
{
    public function __construct(
        public int $id,
        public string $name,
        public string $sellingPrice,
        public string $vendorName,
        public ?string $vendorSlug,
        public ?string $vendorLocation,
        public ?string $imageUrl,
        /** @var list<array{id: int, name: string, slug: string}> */
        public array $categories,
    ) {}

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'selling_price' => $this->sellingPrice,
            'vendor_name' => $this->vendorName,
            'vendor_slug' => $this->vendorSlug,
            'vendor_location' => $this->vendorLocation,
            'image_url' => $this->imageUrl,
            'categories' => $this->categories,
        ];
    }
}
