<?php

namespace App\DTOs\Product;

class ProductShowItem
{
    /**
     * @param  list<ProductMediaItem>  $images
     * @param  list<array{id: int, name: string, slug: string}>  $categories
     * @param  list<array{id: int, name: string, slug: string}>  $colors
     */
    public function __construct(
        public int $id,
        public string $name,
        public string $description,
        public string $vendorPrice,
        public string $sellingPrice,
        public string $commissionRate,
        public ?string $materials,
        public ?int $piecesCount,
        public ?int $productionTimeDays,
        public ProductDimensions $dimensions,
        public ProductVendorSummary $vendor,
        public array $images,
        public array $categories,
        public array $colors,
        public ?string $videoUrl,
    ) {}

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'description' => $this->description,
            'vendor_price' => $this->vendorPrice,
            'selling_price' => $this->sellingPrice,
            'commission_rate' => $this->commissionRate,
            'materials' => $this->materials,
            'pieces_count' => $this->piecesCount,
            'production_time_days' => $this->productionTimeDays,
            'dimensions' => $this->dimensions->toArray(),
            'vendor' => $this->vendor->toArray(),
            'images' => array_map(
                static fn (ProductMediaItem $media): array => $media->toArray(),
                $this->images,
            ),
            'categories' => $this->categories,
            'colors' => $this->colors,
            'video_url' => $this->videoUrl,
        ];
    }
}
