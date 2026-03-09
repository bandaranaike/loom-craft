<?php

namespace App\Services;

use App\Models\Product;
use App\ValueObjects\ProductStockAvailability;

class ProductStockAvailabilityService
{
    public function forProduct(Product $product, int $requestedQuantity): ProductStockAvailability
    {
        $availableQuantity = $product->pieces_count;
        $productionTimeDays = $product->production_time_days;
        $exceedsAvailableStock = $availableQuantity !== null && $requestedQuantity > $availableQuantity;

        return new ProductStockAvailability(
            $requestedQuantity,
            $availableQuantity,
            $productionTimeDays,
            $exceedsAvailableStock,
            $exceedsAvailableStock ? $this->buildDelayMessage($productionTimeDays) : null,
        );
    }

    public function buildDelayMessage(?int $productionTimeDays): string
    {
        if ($productionTimeDays !== null) {
            return "This quantity is not currently in stock. Your order will require additional production time and is expected to take about {$productionTimeDays} days.";
        }

        return 'This quantity is not currently in stock. Your order will require additional production time.';
    }
}
