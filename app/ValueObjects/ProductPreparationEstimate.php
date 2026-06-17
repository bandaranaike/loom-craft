<?php

namespace App\ValueObjects;

final readonly class ProductPreparationEstimate
{
    public function __construct(
        public int $requestedQuantity,
        public ?int $availableQuantity,
        public int $shortageQuantity,
        public int $setupDays,
        public float $weavingDays,
        public int $bufferDays,
        public int $totalDays,
        public bool $exceedsAvailableStock,
        public bool $exceedsMaximumPreparationDays,
        public int $maximumPreparationDays,
        public ?string $message,
    ) {}

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'requested_quantity' => $this->requestedQuantity,
            'available_quantity' => $this->availableQuantity,
            'shortage_quantity' => $this->shortageQuantity,
            'setup_days' => $this->setupDays,
            'weaving_days' => $this->weavingDays,
            'buffer_days' => $this->bufferDays,
            'total_days' => $this->totalDays,
            'exceeds_available_stock' => $this->exceedsAvailableStock,
            'exceeds_maximum_preparation_days' => $this->exceedsMaximumPreparationDays,
            'maximum_preparation_days' => $this->maximumPreparationDays,
            'message' => $this->message,
        ];
    }
}
