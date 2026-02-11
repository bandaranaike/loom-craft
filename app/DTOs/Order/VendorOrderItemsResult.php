<?php

namespace App\DTOs\Order;

class VendorOrderItemsResult
{
    /**
     * @param  list<VendorOrderItemSummary>  $items
     */
    public function __construct(
        public array $items,
    ) {}

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'items' => array_map(
                static fn (VendorOrderItemSummary $item): array => $item->toArray(),
                $this->items,
            ),
        ];
    }
}
