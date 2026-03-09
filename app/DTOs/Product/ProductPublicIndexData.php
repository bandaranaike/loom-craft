<?php

namespace App\DTOs\Product;

use App\Http\Requests\Product\IndexPublicProductsRequest;

class ProductPublicIndexData
{
    public function __construct(
        public ?string $search,
        public ?string $category,
        public ?string $vendor,
        /** @var list<string> */
        public array $colors,
        public ?float $minPrice,
        public ?float $maxPrice,
        public int $perPage,
    ) {}

    public static function fromRequest(IndexPublicProductsRequest $request): self
    {
        return new self(
            $request->string('search')->toString() ?: null,
            $request->string('category')->toString() ?: null,
            $request->string('vendor')->toString() ?: null,
            array_values(array_filter(
                $request->array('colors'),
                static fn (mixed $color): bool => is_string($color) && $color !== ''
            )),
            is_numeric($request->input('min_price')) ? (float) $request->input('min_price') : null,
            is_numeric($request->input('max_price')) ? (float) $request->input('max_price') : null,
            $request->integer('per_page') ?: 9,
        );
    }
}
