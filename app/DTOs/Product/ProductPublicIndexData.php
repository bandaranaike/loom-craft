<?php

namespace App\DTOs\Product;

use App\Http\Requests\Product\IndexPublicProductsRequest;

class ProductPublicIndexData
{
    public function __construct(
        public ?string $search,
        public ?string $category,
        /** @var list<string> */
        public array $colors,
        public int $perPage,
    ) {}

    public static function fromRequest(IndexPublicProductsRequest $request): self
    {
        return new self(
            $request->string('search')->toString() ?: null,
            $request->string('category')->toString() ?: null,
            array_values(array_filter(
                $request->array('colors'),
                static fn (mixed $color): bool => is_string($color) && $color !== ''
            )),
            $request->integer('per_page') ?: 9,
        );
    }
}
