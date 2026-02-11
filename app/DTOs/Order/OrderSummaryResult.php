<?php

namespace App\DTOs\Order;

class OrderSummaryResult
{
    /**
     * @param  list<OrderItemSummary>  $items
     * @param  list<OrderAddressSummary>  $addresses
     */
    public function __construct(
        public int $id,
        public string $status,
        public string $currency,
        public string $subtotal,
        public string $commissionTotal,
        public string $total,
        public string $shippingResponsibility,
        public ?string $placedAt,
        public string $paymentMethod,
        public string $paymentStatus,
        public array $items,
        public array $addresses,
    ) {}

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'status' => $this->status,
            'currency' => $this->currency,
            'subtotal' => $this->subtotal,
            'commission_total' => $this->commissionTotal,
            'total' => $this->total,
            'shipping_responsibility' => $this->shippingResponsibility,
            'placed_at' => $this->placedAt,
            'payment_method' => $this->paymentMethod,
            'payment_status' => $this->paymentStatus,
            'items' => array_map(
                static fn (OrderItemSummary $item): array => $item->toArray(),
                $this->items,
            ),
            'addresses' => array_map(
                static fn (OrderAddressSummary $address): array => $address->toArray(),
                $this->addresses,
            ),
        ];
    }
}
