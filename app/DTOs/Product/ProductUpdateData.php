<?php

namespace App\DTOs\Product;

use App\Http\Requests\Vendor\UpdateProductRequest;
use App\Models\Product;
use App\Models\User;
use App\ValueObjects\Money;

class ProductUpdateData
{
    public function __construct(
        public User $user,
        public Product $product,
        public string $productCode,
        public string $name,
        public string $description,
        public Money $vendorPrice,
        public ?string $discountPercentage,
        public ?string $materials,
        public ?int $piecesCount,
        public ?int $productionTimeDays,
        public ?string $dimensionUnit,
        /** @var list<int> */
        public array $categoryIds,
        /** @var list<int> */
        public array $colorIds,
        /** @var list<array{id: int|null, label: string, vendor_price: Money, dimension_length: float|null, dimension_width: float|null, dimension_height: float|null}> */
        public array $variations,
    ) {}

    public static function fromRequest(UpdateProductRequest $request, Product $product): self
    {
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
            $product,
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
        );
    }

    /**
     * @return list<array{id: int|null, label: string, vendor_price: Money, dimension_length: float|null, dimension_width: float|null, dimension_height: float|null}>
     */
    private static function variationsFromRequest(UpdateProductRequest $request): array
    {
        return collect($request->array('variations'))
            ->map(static function (array $variation): array {
                $length = $variation['dimension_length'] ?? null;
                $width = $variation['dimension_width'] ?? null;
                $height = $variation['dimension_height'] ?? null;

                return [
                    'id' => isset($variation['id']) && is_numeric($variation['id']) ? (int) $variation['id'] : null,
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
