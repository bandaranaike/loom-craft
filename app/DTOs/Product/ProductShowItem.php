<?php

namespace App\DTOs\Product;

class ProductShowItem
{
    /**
     * @param  list<ProductMediaItem>  $images
     * @param  list<array{id: int, name: string, slug: string}>  $categories
     * @param  list<array{id: int, name: string, slug: string}>  $colors
     * @param  list<array{id: int, label: string, vendor_price: string, original_price: string, selling_price: string, dimensions: array{length: float|null, width: float|null, height: float|null, unit: string|null}}>  $variations
     */
    public function __construct(
        public int $id,
        public string $slug,
        public string $productCode,
        public string $name,
        public string $description,
        public string $vendorPrice,
        public string $originalPrice,
        public string $sellingPrice,
        public string $effectiveDiscountPercentage,
        public bool $hasDiscount,
        public string $commissionRate,
        public ?string $materials,
        public ?int $piecesCount,
        public ?int $productionTimeDays,
        public ?string $expiryInformation,
        public ProductDimensions $dimensions,
        public ProductVendorSummary $vendor,
        public array $images,
        public array $categories,
        public array $colors,
        public array $variations,
        public ?string $videoUrl,
    ) {}

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'slug' => $this->slug,
            'product_code' => $this->productCode,
            'name' => $this->name,
            'description' => $this->description,
            'vendor_price' => $this->vendorPrice,
            'original_price' => $this->originalPrice,
            'selling_price' => $this->sellingPrice,
            'effective_discount_percentage' => $this->effectiveDiscountPercentage,
            'has_discount' => $this->hasDiscount,
            'commission_rate' => $this->commissionRate,
            'materials' => $this->materials,
            'pieces_count' => $this->piecesCount,
            'production_time_days' => $this->productionTimeDays,
            'expiry_information' => $this->expiryInformation,
            'dimensions' => $this->dimensions->toArray(),
            'vendor' => $this->vendor->toArray(),
            'images' => array_map(
                static fn (ProductMediaItem $media): array => $media->toArray(),
                $this->images,
            ),
            'categories' => $this->categories,
            'colors' => $this->colors,
            'variations' => $this->variations,
            'video_url' => $this->videoUrl,
        ];
    }
}
