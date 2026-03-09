<?php

namespace App\DTOs\Product;

use App\Http\Requests\Vendor\UpdateProductRequest;
use App\Models\Product;
use App\Models\User;
use App\ValueObjects\Dimensions;
use App\ValueObjects\Money;

class ProductUpdateData
{
    public function __construct(
        public User $user,
        public Product $product,
        public string $name,
        public string $description,
        public Money $vendorPrice,
        public ?string $discountPercentage,
        public ?string $materials,
        public ?int $piecesCount,
        public ?int $productionTimeDays,
        public Dimensions $dimensions,
        /** @var list<int> */
        public array $categoryIds,
        /** @var list<int> */
        public array $colorIds,
    ) {}

    public static function fromRequest(UpdateProductRequest $request, Product $product): self
    {
        $length = $request->input('dimension_length');
        $width = $request->input('dimension_width');
        $height = $request->input('dimension_height');
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
            $request->string('name')->toString(),
            $request->string('description')->toString(),
            Money::fromString($request->string('vendor_price')->toString()),
            $request->filled('discount_percentage')
                ? $request->string('discount_percentage')->toString()
                : null,
            $request->string('materials')->toString() ?: null,
            $request->integer('pieces_count') ?: null,
            $request->integer('production_time_days') ?: null,
            new Dimensions(
                is_numeric($length) ? (float) $length : null,
                is_numeric($width) ? (float) $width : null,
                is_numeric($height) ? (float) $height : null,
                $request->string('dimension_unit')->toString() ?: null,
            ),
            array_map(static fn (int|string $categoryId): int => (int) $categoryId, $categoryIds),
            array_map(static fn (int|string $colorId): int => (int) $colorId, $colorIds),
        );
    }
}
