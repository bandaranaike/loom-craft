<?php

namespace App\ValueObjects;

final readonly class ProductStockAvailability
{
    public function __construct(
        public int $requestedQuantity,
        public ?int $availableQuantity,
        public ?int $productionTimeDays,
        public bool $exceedsAvailableStock,
        public ?string $stockDelayMessage,
    ) {}

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'requested_quantity' => $this->requestedQuantity,
            'available_quantity' => $this->availableQuantity,
            'production_time_days' => $this->productionTimeDays,
            'exceeds_available_stock' => $this->exceedsAvailableStock,
            'stock_delay_message' => $this->stockDelayMessage,
        ];
    }
}
