<?php

namespace App\Services;

use App\Models\Product;
use App\ValueObjects\ProductStockAvailability;

class ProductStockAvailabilityService
{
    public function __construct(private ProductPreparationEstimator $productPreparationEstimator) {}

    public function forProduct(Product $product, int $requestedQuantity): ProductStockAvailability
    {
        $preparationEstimate = $this->productPreparationEstimator->forProduct($product, $requestedQuantity);
        $availableQuantity = $product->pieces_count;
        $productionTimeDays = $product->production_time_days;

        return new ProductStockAvailability(
            $requestedQuantity,
            $availableQuantity,
            $productionTimeDays,
            $preparationEstimate->shortageQuantity,
            $preparationEstimate->setupDays,
            $preparationEstimate->weavingDays,
            $preparationEstimate->bufferDays,
            $preparationEstimate->totalDays,
            $preparationEstimate->exceedsAvailableStock,
            $preparationEstimate->message,
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
