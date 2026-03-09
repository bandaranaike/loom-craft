<?php

namespace App\Services;

use App\Models\Product;
use App\Models\ProductCategory;
use App\ValueObjects\Money;
use App\ValueObjects\ProductPricing;
use Illuminate\Database\Eloquent\Collection;

class ProductPricingService
{
    public function forProduct(Product $product): ProductPricing
    {
        $product->loadMissing('categories');

        /** @var Collection<int, ProductCategory> $categories */
        $categories = $product->categories;

        return $this->fromValues(
            (string) $product->selling_price,
            $this->normalizePercentage($product->discount_percentage),
            $categories,
        );
    }

    /**
     * @param  iterable<ProductCategory>  $categories
     */
    public function fromValues(
        string $originalPrice,
        ?string $productDiscountPercentage = null,
        iterable $categories = [],
    ): ProductPricing {
        $normalizedOriginalPrice = Money::fromString($originalPrice)->amount;
        $normalizedProductDiscount = $this->normalizePercentage($productDiscountPercentage);
        $categoryDiscount = $this->highestCategoryDiscount($categories);
        $effectiveDiscount = $this->maxPercentage($normalizedProductDiscount, $categoryDiscount);
        $hasDiscount = (float) $effectiveDiscount > 0;

        if (! $hasDiscount) {
            return new ProductPricing(
                $normalizedOriginalPrice,
                $normalizedOriginalPrice,
                $normalizedProductDiscount,
                $categoryDiscount,
                $effectiveDiscount,
                false,
            );
        }

        $discountAmount = Money::fromString($normalizedOriginalPrice)
            ->percentageOf($effectiveDiscount);
        $discountedPrice = Money::fromString((string) max(
            0,
            (float) $normalizedOriginalPrice - (float) $discountAmount->amount,
        ))->amount;

        return new ProductPricing(
            $normalizedOriginalPrice,
            $discountedPrice,
            $normalizedProductDiscount,
            $categoryDiscount,
            $effectiveDiscount,
            true,
        );
    }

    public function normalizePercentage(null|float|int|string $percentage): string
    {
        if ($percentage === null || $percentage === '') {
            return '0.00';
        }

        return number_format(
            min(100, max(0, (float) $percentage)),
            2,
            '.',
            '',
        );
    }

    /**
     * @param  iterable<ProductCategory>  $categories
     */
    public function highestCategoryDiscount(iterable $categories): string
    {
        $max = 0.0;

        foreach ($categories as $category) {
            if (property_exists($category, 'is_active') && $category->is_active === false) {
                continue;
            }

            $max = max($max, (float) $this->normalizePercentage($category->discount_percentage));
        }

        return number_format($max, 2, '.', '');
    }

    public function maxPercentage(string $first, string $second): string
    {
        return (float) $first >= (float) $second ? $first : $second;
    }
}
