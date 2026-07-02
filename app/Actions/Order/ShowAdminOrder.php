<?php

namespace App\Actions\Order;

use App\DTOs\Order\AdminOrderSummaryResult;
use App\DTOs\Order\OrderAddressSummary;
use App\DTOs\Order\OrderItemSummary;
use App\Models\Order;
use App\Models\OrderAddress;
use App\Models\OrderItem;
use App\Models\Payment;
use App\Models\ShippingCarrier;
use App\Models\ShippingService;
use App\Services\Fulfillment\FulfillmentStatusService;
use App\ValueObjects\Money;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Storage;

class ShowAdminOrder
{
    public function __construct(
        private readonly FulfillmentStatusService $fulfillmentStatusService,
    ) {}

    public function handle(int $orderId): AdminOrderSummaryResult
    {
        Gate::authorize('viewAny', Order::class);

        $order = Order::query()
            ->with(['items.product.vendor', 'addresses', 'payment', 'user', 'shipments.shippingCarrier', 'shipments.shippingService'])
            ->findOrFail($orderId);

        $payment = $order->payment;

        if (! $payment instanceof Payment) {
            throw new \RuntimeException('Order payment is missing.');
        }

        $items = $order->items->map(function (OrderItem $item): OrderItemSummary {
            $product = $item->product;
            $vendor = $product?->vendor;

            if ($product === null || $vendor === null) {
                throw new \RuntimeException('Order item references missing product or vendor.');
            }

            return new OrderItemSummary(
                $item->id,
                $product->name,
                $vendor->display_name,
                $vendor->slug,
                $item->quantity,
                Money::fromString((string) $item->unit_price)->amount,
                Money::fromString((string) $item->line_total)->amount,
            );
        })->all();

        $addresses = $order->addresses->map(fn (OrderAddress $address): OrderAddressSummary => new OrderAddressSummary(
            $address->type,
            $address->full_name,
            $address->line1,
            $address->line2,
            $address->city,
            $address->region,
            $address->postal_code,
            $address->country_code,
            $address->phone,
        ))->all();

        $shipment = $order->shipments->first();

        return new AdminOrderSummaryResult(
            $order->id,
            $order->public_id,
            $order->order_number,
            $order->status,
            $order->currency,
            Money::fromString((string) $order->subtotal)->amount,
            Money::fromString((string) $order->commission_total)->amount,
            Money::fromString((string) $order->total)->amount,
            $order->shipping_responsibility,
            $order->placed_at?->toDateTimeString(),
            $payment->method,
            $payment->status,
            $order->user?->name ?? $order->guest_name,
            $order->user?->email ?? $order->guest_email,
            $items,
            $addresses,
            $shipment === null ? null : [
                'id' => $shipment->id,
                'shipment_number' => $shipment->shipment_number,
                'status' => $shipment->status,
                'tracking_number' => $shipment->tracking_number,
                'shipping_carrier_id' => $shipment->shipping_carrier_id,
                'shipping_service_id' => $shipment->shipping_service_id,
                'carrier' => $shipment->shippingCarrier?->name ?? $shipment->carrier,
                'service_level' => $shipment->shippingService?->name ?? $shipment->service_level,
                'vendor_preparing_at' => $shipment->vendor_preparing_at?->toDateTimeString(),
                'vendor_handed_to_admin_at' => $shipment->vendor_handed_to_admin_at?->toDateTimeString(),
                'admin_received_at' => $shipment->admin_received_at?->toDateTimeString(),
                'quality_checked_at' => $shipment->quality_checked_at?->toDateTimeString(),
                'packed_at' => $shipment->packed_at?->toDateTimeString(),
                'shipped_at' => $shipment->shipped_at?->toDateTimeString(),
                'delivered_at' => $shipment->delivered_at?->toDateTimeString(),
                'delivery_recipient_name' => $shipment->delivery_recipient_name,
                'delivery_proof_reference' => $shipment->delivery_proof_reference,
                'delivery_evidence_url' => is_string($shipment->delivery_evidence_path) ? Storage::disk('public')->url($shipment->delivery_evidence_path) : null,
                'delivery_note' => $shipment->delivery_note,
                'delivery_exception_reason' => $shipment->delivery_exception_reason,
                'delivery_exception_note' => $shipment->delivery_exception_note,
                'failed_delivery_attempts' => $shipment->failed_delivery_attempts,
            ],
            $this->paymentProof($payment),
            $this->fulfillmentStatusService->paymentStatusOptionsFor($payment),
            $this->fulfillmentStatusService->allowedNextOrderStatuses($order, auth()->user()),
            $shipment === null ? [] : $this->fulfillmentStatusService->allowedNextShipmentStatuses($order, $shipment, auth()->user()),
            $this->shippingCarriers(),
            Gate::allows('manageOffline', $order),
            Gate::allows('delete', $order),
        );
    }

    /**
     * @return array{url: string, original_name: string, mime_type: string, uploaded_at: ?string}|null
     */
    private function paymentProof(Payment $payment): ?array
    {
        if (! is_string($payment->bank_transfer_slip_path) || $payment->bank_transfer_slip_path === '') {
            return null;
        }

        return [
            'url' => Storage::disk('public')->url($payment->bank_transfer_slip_path),
            'original_name' => $payment->bank_transfer_slip_original_name ?? 'bank-transfer-slip',
            'mime_type' => $payment->bank_transfer_slip_mime_type ?? 'application/octet-stream',
            'uploaded_at' => $payment->bank_transfer_slip_uploaded_at?->toDateTimeString(),
        ];
    }

    /**
     * @return list<array{id: int, name: string, services: list<array{id: int, name: string}>}>
     */
    private function shippingCarriers(): array
    {
        return ShippingCarrier::query()
            ->where('is_active', true)
            ->with(['services' => fn ($query) => $query
                ->where('is_active', true)
                ->orderBy('sort_order')
                ->orderBy('name')])
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get()
            ->map(static fn (ShippingCarrier $carrier): array => [
                'id' => $carrier->id,
                'name' => $carrier->name,
                'services' => $carrier->services
                    ->map(static fn (ShippingService $service): array => [
                        'id' => $service->id,
                        'name' => $service->name,
                    ])
                    ->all(),
            ])
            ->all();
    }
}
