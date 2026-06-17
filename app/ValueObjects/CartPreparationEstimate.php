<?php

namespace App\ValueObjects;

final readonly class CartPreparationEstimate
{
    public function __construct(
        public int $distinctProductCount,
        public int $largeCartThreshold,
        public bool $exceedsLargeCartThreshold,
        public int $totalDays,
        public bool $hasProductionDelay,
        public bool $exceedsMaximumPreparationDays,
        public int $maximumPreparationDays,
        public ?string $message,
        public ?string $workloadWarningMessage,
    ) {}

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'distinct_product_count' => $this->distinctProductCount,
            'large_cart_threshold' => $this->largeCartThreshold,
            'exceeds_large_cart_threshold' => $this->exceedsLargeCartThreshold,
            'total_days' => $this->totalDays,
            'has_production_delay' => $this->hasProductionDelay,
            'exceeds_maximum_preparation_days' => $this->exceedsMaximumPreparationDays,
            'maximum_preparation_days' => $this->maximumPreparationDays,
            'message' => $this->message,
            'workload_warning_message' => $this->workloadWarningMessage,
        ];
    }
}
