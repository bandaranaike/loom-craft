<?php

namespace App\DTOs\Product;

class ProductShowResult
{
    public function __construct(
        public ProductShowItem $product,
    ) {}

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'product' => $this->product->toArray(),
        ];
    }
}
