<?php

namespace App\DTOs\Order;

class AdminOrderSummaryResult
{
    /**
     * @param  list<OrderItemSummary>  $items
     * @param  list<OrderAddressSummary>  $addresses
     * @param  array{url: string, original_name: string, mime_type: string, uploaded_at: ?string}|null  $paymentProof
     * @param  list<string>  $paymentStatusOptions
     * @param  list<string>  $orderStatusOptions
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
        public ?string $customerName,
        public ?string $customerEmail,
        public array $items,
        public array $addresses,
        public ?array $paymentProof,
        public array $paymentStatusOptions,
        public array $orderStatusOptions,
        public bool $canManageOffline,
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
            'customer_name' => $this->customerName,
            'customer_email' => $this->customerEmail,
            'payment_proof' => $this->paymentProof,
            'payment_status_options' => $this->paymentStatusOptions,
            'order_status_options' => $this->orderStatusOptions,
            'can_manage_offline' => $this->canManageOffline,
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
