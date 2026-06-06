<?php

namespace App\ValueObjects;

final readonly class ProductStockAvailability
{
    public function __construct(
        public int $requestedQuantity,
        public ?int $availableQuantity,
        public ?int $productionTimeDays,
        public int $shortageQuantity,
        public int $preparationSetupDays,
        public float $preparationWeavingDays,
        public int $preparationBufferDays,
        public int $preparationTimeDays,
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
            'shortage_quantity' => $this->shortageQuantity,
            'preparation_setup_days' => $this->preparationSetupDays,
            'preparation_weaving_days' => $this->preparationWeavingDays,
            'preparation_buffer_days' => $this->preparationBufferDays,
            'preparation_time_days' => $this->preparationTimeDays,
            'exceeds_available_stock' => $this->exceedsAvailableStock,
            'stock_delay_message' => $this->stockDelayMessage,
        ];
    }
}
