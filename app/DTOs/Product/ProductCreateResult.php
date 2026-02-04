<?php

namespace App\DTOs\Product;

class ProductCreateResult
{
    public function __construct(
        public int $productId,
        public string $sellingPrice,
    ) {}
}
