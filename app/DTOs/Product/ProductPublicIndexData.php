<?php

namespace App\DTOs\Product;

use App\Http\Requests\Product\IndexPublicProductsRequest;

class ProductPublicIndexData
{
    public function __construct(
        public ?string $search,
        public int $perPage,
    ) {}

    public static function fromRequest(IndexPublicProductsRequest $request): self
    {
        return new self(
            $request->string('search')->toString() ?: null,
            $request->integer('per_page') ?: 9,
        );
    }
}
