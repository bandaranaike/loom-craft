<?php

namespace App\Services;

use App\Models\Product;
use App\ValueObjects\CartPreparationEstimate;
use App\ValueObjects\ProductPreparationEstimate;
use Illuminate\Support\Collection;

class ProductPreparationEstimator
{
    public function forProduct(Product $product, int $requestedQuantity): ProductPreparationEstimate
    {
        $availableQuantity = max(0, (int) ($product->pieces_count ?? 0));
        $shortageQuantity = max(0, $requestedQuantity - $availableQuantity);
        $exceedsAvailableStock = $shortageQuantity > 0;

        if (! $exceedsAvailableStock) {
            return new ProductPreparationEstimate(
                requestedQuantity: $requestedQuantity,
                availableQuantity: $product->pieces_count,
                shortageQuantity: 0,
                setupDays: 0,
                weavingDays: 0.0,
                bufferDays: 0,
                totalDays: 0,
                exceedsAvailableStock: false,
                exceedsMaximumPreparationDays: false,
                maximumPreparationDays: $this->maximumDisplayDays(),
                message: null,
            );
        }

        $setupDays = (int) config('commerce.production_time_setup_days', 2);
        $weavingDays = $this->resolveWeavingDays($product) * $shortageQuantity;
        $bufferDays = (int) ceil(($setupDays + $weavingDays) * $this->bufferRate());
        $calculatedTotalDays = (int) ceil($setupDays + $weavingDays + $bufferDays);
        $maximumDisplayDays = $this->maximumDisplayDays();
        $exceedsMaximumPreparationDays = $calculatedTotalDays > $maximumDisplayDays;
        $totalDays = $exceedsMaximumPreparationDays ? $maximumDisplayDays : $calculatedTotalDays;

        return new ProductPreparationEstimate(
            requestedQuantity: $requestedQuantity,
            availableQuantity: $product->pieces_count,
            shortageQuantity: $shortageQuantity,
            setupDays: $setupDays,
            weavingDays: $weavingDays,
            bufferDays: $bufferDays,
            totalDays: $totalDays,
            exceedsAvailableStock: true,
            exceedsMaximumPreparationDays: $exceedsMaximumPreparationDays,
            maximumPreparationDays: $maximumDisplayDays,
            message: $this->buildProductMessage($shortageQuantity, $totalDays, $exceedsMaximumPreparationDays),
        );
    }

    /**
     * @param  Collection<int, ProductPreparationEstimate>  $estimates
     */
    public function forCart(Collection $estimates): CartPreparationEstimate
    {
        $distinctProductCount = $estimates->count();
        $largeCartThreshold = (int) config('commerce.production_time_large_cart_threshold', 6);
        $exceedsLargeCartThreshold = $distinctProductCount > $largeCartThreshold;
        $totalDays = (int) $estimates->max('totalDays');
        $hasProductionDelay = $estimates->contains(
            static fn (ProductPreparationEstimate $estimate): bool => $estimate->exceedsAvailableStock,
        );
        $exceedsMaximumPreparationDays = $estimates->contains(
            static fn (ProductPreparationEstimate $estimate): bool => $estimate->exceedsMaximumPreparationDays,
        );
        $maximumDisplayDays = $this->maximumDisplayDays();
        $workloadWarningMessage = $exceedsLargeCartThreshold
            ? 'The various product count is big in your cart and it may take longer than expected due to workload.'
            : null;

        return new CartPreparationEstimate(
            distinctProductCount: $distinctProductCount,
            largeCartThreshold: $largeCartThreshold,
            exceedsLargeCartThreshold: $exceedsLargeCartThreshold,
            totalDays: $totalDays,
            hasProductionDelay: $hasProductionDelay,
            exceedsMaximumPreparationDays: $exceedsMaximumPreparationDays,
            maximumPreparationDays: $maximumDisplayDays,
            message: $this->buildCartMessage($totalDays, $hasProductionDelay, $workloadWarningMessage, $exceedsMaximumPreparationDays),
            workloadWarningMessage: $workloadWarningMessage,
        );
    }

    public function buildProductMessage(int $shortageQuantity, int $totalDays, bool $exceedsMaximumPreparationDays): string
    {
        $pieceLabel = $shortageQuantity === 1 ? 'piece' : 'pieces';
        $displayDays = $this->formatDisplayDays($totalDays, $exceedsMaximumPreparationDays);

        if ($exceedsMaximumPreparationDays) {
            return "This quantity is not currently in stock. {$shortageQuantity} {$pieceLabel} will need production and the preparation time is expected to take {$displayDays}. The product order time is getting longer. Before placing this order, you must contact the vendor.";
        }

        return "This quantity is not currently in stock. {$shortageQuantity} {$pieceLabel} will need production and the preparation time is expected to take about {$displayDays}.";
    }

    private function buildCartMessage(int $totalDays, bool $hasProductionDelay, ?string $workloadWarningMessage, bool $exceedsMaximumPreparationDays): ?string
    {
        if (! $hasProductionDelay && $workloadWarningMessage === null) {
            return null;
        }

        $message = match (true) {
            $hasProductionDelay && $exceedsMaximumPreparationDays => "Your order preparation time is expected to take {$this->formatDisplayDays($totalDays, true)} because all product preparation runs in parallel. The product order time is getting longer. Before placing this order, you must contact the vendor.",
            $hasProductionDelay => "Your order preparation time is expected to take about {$this->formatDisplayDays($totalDays, false)} because all product preparation runs in parallel.",
            default => 'Your selected pieces are available now.',
        };

        if ($workloadWarningMessage !== null) {
            $message .= ' '.$workloadWarningMessage;
        }

        return $message;
    }

    private function resolveWeavingDays(Product $product): float
    {
        if ($product->production_time_days !== null) {
            return (float) $product->production_time_days;
        }

        return (float) config('commerce.production_time_default_weaving_days', 1);
    }

    private function bufferRate(): float
    {
        return max(0.0, (float) config('commerce.production_time_buffer_rate', 0.10));
    }

    private function maximumDisplayDays(): int
    {
        return max(1, (int) config('commerce.production_time_max_display_days', 60));
    }

    private function formatDisplayDays(int $totalDays, bool $exceedsMaximumPreparationDays): string
    {
        return $exceedsMaximumPreparationDays ? "{$totalDays}+ days" : "{$totalDays} days";
    }
}
