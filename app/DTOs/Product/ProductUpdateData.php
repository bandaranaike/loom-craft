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
        public ?string $materials,
        public ?int $piecesCount,
        public ?int $productionTimeDays,
        public Dimensions $dimensions,
    ) {}

    public static function fromRequest(UpdateProductRequest $request, Product $product): self
    {
        $length = $request->input('dimension_length');
        $width = $request->input('dimension_width');
        $height = $request->input('dimension_height');

        return new self(
            $request->user(),
            $product,
            $request->string('name')->toString(),
            $request->string('description')->toString(),
            Money::fromString($request->string('vendor_price')->toString()),
            $request->string('materials')->toString() ?: null,
            $request->integer('pieces_count') ?: null,
            $request->integer('production_time_days') ?: null,
            new Dimensions(
                is_numeric($length) ? (float) $length : null,
                is_numeric($width) ? (float) $width : null,
                is_numeric($height) ? (float) $height : null,
                $request->string('dimension_unit')->toString() ?: null,
            ),
        );
    }
}
