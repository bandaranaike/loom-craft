<?php

namespace App\DTOs\Order;

class OrderSummaryResult
{
    /**
     * @param  list<OrderItemSummary>  $items
     * @param  list<OrderAddressSummary>  $addresses
     * @param  array{url: string, original_name: string, mime_type: string, uploaded_at: ?string}|null  $paymentProof
     * @param  array{
     *      is_cancelled: bool,
     *      summary: array{title: string, description: string}|null,
     *      steps: list<array{key: string, label: string, state: string}>
     *  }|null  $progress
     */
    public function __construct(
        public int $id,
        public ?string $publicId,
        public string $status,
        public string $currency,
        public string $subtotal,
        public string $commissionTotal,
        public string $total,
        public string $shippingResponsibility,
        public ?string $placedAt,
        public string $paymentMethod,
        public string $paymentStatus,
        public ?string $paymentAmount,
        public ?string $paymentCurrency,
        public ?string $paymentOriginalAmount,
        public ?string $paymentOriginalCurrency,
        public array $items,
        public array $addresses,
        public ?array $paymentProof,
        public ?array $progress = null,
        public bool $canUploadPaymentProof = false,
    ) {}

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'public_id' => $this->publicId,
            'status' => $this->status,
            'currency' => $this->currency,
            'subtotal' => $this->subtotal,
            'commission_total' => $this->commissionTotal,
            'total' => $this->total,
            'shipping_responsibility' => $this->shippingResponsibility,
            'placed_at' => $this->placedAt,
            'payment_method' => $this->paymentMethod,
            'payment_status' => $this->paymentStatus,
            'payment_amount' => $this->paymentAmount,
            'payment_currency' => $this->paymentCurrency,
            'payment_original_amount' => $this->paymentOriginalAmount,
            'payment_original_currency' => $this->paymentOriginalCurrency,
            'payment_proof' => $this->paymentProof,
            'progress' => $this->progress,
            'can_upload_payment_proof' => $this->canUploadPaymentProof,
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
