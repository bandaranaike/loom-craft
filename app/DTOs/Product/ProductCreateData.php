<?php

namespace App\DTOs\Product;

use App\Http\Requests\Vendor\StoreProductRequest;
use App\Models\User;
use App\ValueObjects\Money;
use Illuminate\Http\UploadedFile;

class ProductCreateData
{
    /**
     * @param  list<UploadedFile>  $images
     * @param  list<int>  $categoryIds
     * @param  list<int>  $colorIds
     * @param  list<array{label: string, vendor_price: Money, dimension_length: float|null, dimension_width: float|null, dimension_height: float|null}>  $variations
     */
    public function __construct(
        public User $user,
        public string $productCode,
        public string $name,
        public string $description,
        public Money $vendorPrice,
        public ?string $discountPercentage,
        public ?string $materials,
        public ?int $piecesCount,
        public ?int $productionTimeDays,
        public ?string $dimensionUnit,
        public array $categoryIds,
        public array $colorIds,
        public array $variations,
        public array $images,
        public ?UploadedFile $video,
    ) {}

    public static function fromRequest(StoreProductRequest $request): self
    {
        $images = array_values(array_filter(
            $request->file('images', []),
            static fn ($file): bool => $file instanceof UploadedFile && $file->isValid()
        ));

        $video = $request->file('video');
        $categoryIds = array_values(array_unique(array_filter(
            $request->array('category_ids'),
            static fn (mixed $categoryId): bool => is_numeric($categoryId)
        )));
        $colorIds = array_values(array_unique(array_filter(
            $request->array('color_ids'),
            static fn (mixed $colorId): bool => is_numeric($colorId)
        )));

        return new self(
            $request->user(),
            $request->string('product_code')->toString(),
            $request->string('name')->toString(),
            $request->string('description')->toString(),
            Money::fromString($request->string('vendor_price')->toString()),
            $request->filled('discount_percentage')
                ? $request->string('discount_percentage')->toString()
                : null,
            $request->string('materials')->toString() ?: null,
            $request->integer('pieces_count') ?: null,
            $request->integer('production_time_days') ?: null,
            $request->string('dimension_unit')->toString() ?: null,
            array_map(static fn (int|string $categoryId): int => (int) $categoryId, $categoryIds),
            array_map(static fn (int|string $colorId): int => (int) $colorId, $colorIds),
            self::variationsFromRequest($request),
            $images,
            $video instanceof UploadedFile && $video->isValid() ? $video : null,
        );
    }

    /**
     * @return list<array{label: string, vendor_price: Money, dimension_length: float|null, dimension_width: float|null, dimension_height: float|null}>
     */
    private static function variationsFromRequest(StoreProductRequest $request): array
    {
        return collect($request->array('variations'))
            ->map(static function (array $variation): array {
                $length = $variation['dimension_length'] ?? null;
                $width = $variation['dimension_width'] ?? null;
                $height = $variation['dimension_height'] ?? null;

                return [
                    'label' => trim((string) ($variation['label'] ?? '')),
                    'vendor_price' => Money::fromString((string) ($variation['vendor_price'] ?? '0')),
                    'dimension_length' => is_numeric($length) ? (float) $length : null,
                    'dimension_width' => is_numeric($width) ? (float) $width : null,
                    'dimension_height' => is_numeric($height) ? (float) $height : null,
                ];
            })
            ->values()
            ->all();
    }
}
