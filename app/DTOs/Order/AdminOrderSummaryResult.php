<?php

namespace App\DTOs\Order;

class AdminOrderSummaryResult
{
    /**
     * @param  list<OrderItemSummary>  $items
     * @param  list<OrderAddressSummary>  $addresses
     * @param  array{id: int, shipment_number: string|null, status: string, tracking_number: string|null, shipping_carrier_id: int|null, shipping_service_id: int|null, carrier: string|null, service_level: string|null, vendor_preparing_at: string|null, vendor_handed_to_admin_at: string|null, admin_received_at: string|null, quality_checked_at: string|null, packed_at: string|null, shipped_at: string|null, delivered_at: string|null}|null  $shipment
     * @param  array{url: string, original_name: string, mime_type: string, uploaded_at: ?string}|null  $paymentProof
     * @param  list<string>  $paymentStatusOptions
     * @param  list<string>  $orderStatusOptions
     * @param  list<string>  $shipmentStatusOptions
     * @param  list<array{id: int, name: string, services: list<array{id: int, name: string}>}>  $shippingCarriers
     */
    public function __construct(
        public int $id,
        public ?string $publicId,
        public ?string $orderNumber,
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
        public ?array $shipment,
        public ?array $paymentProof,
        public array $paymentStatusOptions,
        public array $orderStatusOptions,
        public array $shipmentStatusOptions,
        public array $shippingCarriers,
        public bool $canManageOffline,
        public bool $canDelete,
    ) {}

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'public_id' => $this->publicId,
            'order_number' => $this->orderNumber,
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
            'shipment' => $this->shipment,
            'payment_proof' => $this->paymentProof,
            'payment_status_options' => $this->paymentStatusOptions,
            'order_status_options' => $this->orderStatusOptions,
            'shipment_status_options' => $this->shipmentStatusOptions,
            'shipping_carriers' => $this->shippingCarriers,
            'can_manage_offline' => $this->canManageOffline,
            'can_delete' => $this->canDelete,
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
