<?php

namespace App\DTOs\Order;

class VendorOrderItemSummary
{
    public function __construct(
        public int $orderId,
        public int $orderItemId,
        public string $status,
        public string $currency,
        public string $productName,
        public int $quantity,
        public string $lineTotal,
        public string $shippingResponsibility,
        public ?string $placedAt,
    ) {}

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'order_id' => $this->orderId,
            'order_item_id' => $this->orderItemId,
            'status' => $this->status,
            'currency' => $this->currency,
            'product_name' => $this->productName,
            'quantity' => $this->quantity,
            'line_total' => $this->lineTotal,
            'shipping_responsibility' => $this->shippingResponsibility,
            'placed_at' => $this->placedAt,
        ];
    }
}
