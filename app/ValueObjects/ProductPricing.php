<?php

namespace App\ValueObjects;

final readonly class ProductPricing
{
    public function __construct(
        public string $originalPrice,
        public string $discountedPrice,
        public string $productDiscountPercentage,
        public string $categoryDiscountPercentage,
        public string $effectiveDiscountPercentage,
        public bool $hasDiscount,
    ) {}
}
