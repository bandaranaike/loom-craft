<?php

namespace App\Services\Fulfillment;

use App\Enums\FulfillmentStatusDomain;
use App\Enums\OrderStatus;
use App\Enums\PaymentStatus;
use App\Enums\ShipmentStatus;
use App\Models\FulfillmentStatusHistory;
use App\Models\Order;
use App\Models\Payment;
use App\Models\Shipment;
use App\Models\User;
use InvalidArgumentException;

class FulfillmentStatusService
{
    /**
     * @return list<string>
     */
    public function orderStatusOptions(): array
    {
        return OrderStatus::values();
    }

    /**
     * @return list<string>
     */
    public function orderFilterOptions(): array
    {
        return OrderStatus::values();
    }

    /**
     * @return list<string>
     */
    public function paymentStatusOptionsFor(Payment $payment): array
    {
        if ($payment->method === 'cod') {
            return [
                PaymentStatus::CollectionPending->value,
                PaymentStatus::Paid->value,
                PaymentStatus::Failed->value,
            ];
        }

        if ($payment->method === 'bank_transfer') {
            return [
                PaymentStatus::Pending->value,
                PaymentStatus::Paid->value,
                PaymentStatus::Failed->value,
            ];
        }

        return PaymentStatus::values();
    }

    /**
     * @return list<string>
     */
    public function allowedNextOrderStatuses(Order $order, User $actor): array
    {
        if ($actor->role !== 'admin') {
            return [];
        }

        return match ($this->resolveOrderStatus($order->status)) {
            OrderStatus::Pending => [OrderStatus::Paid->value, OrderStatus::Cancelled->value],
            OrderStatus::Paid => [OrderStatus::Confirmed->value, OrderStatus::Cancelled->value],
            OrderStatus::Confirmed => [OrderStatus::Fulfilled->value, OrderStatus::Cancelled->value],
            OrderStatus::Fulfilled => [OrderStatus::Closed->value],
            OrderStatus::Closed, OrderStatus::Cancelled => [],
        };
    }

    /**
     * @return list<string>
     */
    public function allowedNextShipmentStatuses(Order $order, Shipment $shipment, User $actor): array
    {
        if ($shipment->order_id !== $order->id) {
            return [];
        }

        if ($actor->role === 'vendor') {
            if (! in_array($order->status, [OrderStatus::Paid->value, OrderStatus::Confirmed->value], true)) {
                return [];
            }

            return match ($this->resolveShipmentStatus($shipment->status)) {
                ShipmentStatus::Pending => [ShipmentStatus::VendorPreparing->value],
                ShipmentStatus::VendorPreparing => [ShipmentStatus::VendorHandedToAdmin->value],
                ShipmentStatus::ReadyForPacking => [ShipmentStatus::Packed->value],
                default => [],
            };
        }

        if ($actor->role !== 'admin') {
            return [];
        }

        return match ($this->resolveShipmentStatus($shipment->status)) {
            ShipmentStatus::Pending => [ShipmentStatus::VendorPreparing->value],
            ShipmentStatus::VendorPreparing => [ShipmentStatus::VendorHandedToAdmin->value],
            ShipmentStatus::VendorHandedToAdmin => [ShipmentStatus::AdminReceived->value],
            ShipmentStatus::AdminReceived => [ShipmentStatus::QualityChecked->value],
            ShipmentStatus::QualityChecked => [ShipmentStatus::Packed->value],
            ShipmentStatus::ReadyForPacking => [ShipmentStatus::Packed->value],
            ShipmentStatus::Packed => [ShipmentStatus::ReadyForDispatch->value],
            ShipmentStatus::ReadyForDispatch => [ShipmentStatus::Dispatched->value],
            ShipmentStatus::Dispatched => [
                ShipmentStatus::InTransit->value,
                ShipmentStatus::Delivered->value,
                ShipmentStatus::DeliveryFailed->value,
                ShipmentStatus::ReturnToSender->value,
            ],
            ShipmentStatus::InTransit => [
                ShipmentStatus::Delivered->value,
                ShipmentStatus::DeliveryFailed->value,
                ShipmentStatus::ReturnToSender->value,
            ],
            ShipmentStatus::DeliveryFailed => [
                ShipmentStatus::ReadyForDispatch->value,
                ShipmentStatus::ReturnToSender->value,
            ],
            ShipmentStatus::ReturnToSender => [ShipmentStatus::Returned->value],
            ShipmentStatus::Delivered, ShipmentStatus::Returned => [],
        };
    }

    public function canTransitionOrder(Order $order, string $nextStatus, User $actor): bool
    {
        if (! in_array($nextStatus, $this->allowedNextOrderStatuses($order, $actor), true)) {
            return false;
        }

        return ! ($nextStatus === OrderStatus::Cancelled->value && $this->shipmentHasReachedDispatch($order));
    }

    public function canTransitionShipment(Order $order, Shipment $shipment, string $nextStatus, User $actor): bool
    {
        if (! in_array($nextStatus, $this->allowedNextShipmentStatuses($order, $shipment, $actor), true)) {
            return false;
        }

        return ! ($nextStatus === ShipmentStatus::Dispatched->value && ! $this->shipmentHasTracking($shipment));
    }

    public function canTransitionPayment(Order $order, Payment $payment, string $nextStatus, User $actor): bool
    {
        if ($actor->role !== 'admin' || $payment->order_id !== $order->id) {
            return false;
        }

        $currentStatus = $this->resolvePaymentStatus($payment->status);
        $next = $this->resolvePaymentStatus($nextStatus);

        if ($currentStatus === $next) {
            return true;
        }

        if ($payment->method === 'bank_transfer') {
            return match ($currentStatus) {
                PaymentStatus::Pending => in_array($next, [PaymentStatus::Paid, PaymentStatus::Failed], true),
                default => false,
            };
        }

        if ($payment->method === 'cod') {
            return match ($currentStatus) {
                PaymentStatus::Pending, PaymentStatus::CollectionPending => in_array($next, [PaymentStatus::CollectionPending, PaymentStatus::Paid, PaymentStatus::Failed], true),
                PaymentStatus::Paid => $next === PaymentStatus::Refunded,
                default => false,
            };
        }

        return false;
    }

    public function updateOrderStatus(Order $order, string $nextStatus, User $actor, ?string $reason = null, ?string $note = null): void
    {
        $next = $this->resolveOrderStatus($nextStatus);

        if (! $this->canTransitionOrder($order, $next->value, $actor)) {
            throw new InvalidArgumentException('The requested order status transition is not allowed.');
        }

        if ($next === OrderStatus::Cancelled && $this->shipmentHasReachedDispatch($order)) {
            throw new InvalidArgumentException('Orders cannot be cancelled after dispatch.');
        }

        $fromStatus = $order->status;

        $order->update([
            'status' => $next->value,
        ]);

        $this->recordHistory(
            order: $order,
            domain: FulfillmentStatusDomain::Order,
            actor: $actor,
            fromStatus: $fromStatus,
            toStatus: $next->value,
            reason: $reason,
            note: $note,
        );
    }

    /**
     * @param  array{remitted_amount?: string|null, reference?: string|null, note?: string|null}  $codSettlement
     */
    public function updatePaymentStatus(Order $order, Payment $payment, string $nextStatus, User $actor, ?string $reason = null, ?string $note = null, array $codSettlement = []): void
    {
        $next = $this->resolvePaymentStatus($nextStatus);

        if (! $this->canTransitionPayment($order, $payment, $next->value, $actor)) {
            throw new InvalidArgumentException('The requested payment status transition is not allowed.');
        }

        $fromStatus = $payment->status;
        $updates = [
            'status' => $next->value,
            'verified_by' => $actor->id,
            'verified_at' => now(),
        ];

        if ($payment->method === 'cod' && $next === PaymentStatus::Paid) {
            $updates = [
                ...$updates,
                'cod_collected_amount' => $payment->amount,
                'cod_remitted_amount' => $codSettlement['remitted_amount'] ?? $payment->amount,
                'cod_remittance_reference' => $codSettlement['reference'] ?? null,
                'cod_settlement_note' => $codSettlement['note'] ?? null,
                'cod_settled_by' => $actor->id,
                'cod_settled_at' => now(),
            ];
        }

        $payment->update($updates);

        $this->recordHistory(
            order: $order,
            payment: $payment,
            domain: FulfillmentStatusDomain::Payment,
            actor: $actor,
            fromStatus: $fromStatus,
            toStatus: $next->value,
            reason: $reason,
            note: $note ?? $codSettlement['note'] ?? null,
        );
    }

    public function updateShipmentStatus(Order $order, Shipment $shipment, string $nextStatus, User $actor, ?string $reason = null, ?string $note = null): void
    {
        $next = $this->resolveShipmentStatus($nextStatus);

        if (! $this->canTransitionShipment($order, $shipment, $next->value, $actor)) {
            throw new InvalidArgumentException('The requested shipment status transition is not allowed.');
        }

        $fromStatus = $shipment->status;
        $updates = ['status' => $next->value];

        if ($next === ShipmentStatus::VendorPreparing && $shipment->vendor_preparing_at === null) {
            $updates['vendor_preparing_at'] = now();
        }

        if ($next === ShipmentStatus::VendorHandedToAdmin && $shipment->vendor_handed_to_admin_at === null) {
            $updates['vendor_handed_to_admin_at'] = now();
        }

        if ($next === ShipmentStatus::AdminReceived && $shipment->admin_received_at === null) {
            $updates['admin_received_at'] = now();
        }

        if ($next === ShipmentStatus::QualityChecked && $shipment->quality_checked_at === null) {
            $updates['quality_checked_at'] = now();
        }

        if ($next === ShipmentStatus::Packed && $shipment->packed_at === null) {
            $updates['packed_at'] = now();
        }

        if ($next === ShipmentStatus::Dispatched && $shipment->shipped_at === null) {
            $updates['shipped_at'] = now();
        }

        if ($next === ShipmentStatus::Delivered && $shipment->delivered_at === null) {
            $updates['delivered_at'] = now();
        }

        $shipment->update($updates);

        $this->recordHistory(
            order: $order,
            shipment: $shipment,
            domain: FulfillmentStatusDomain::Shipment,
            actor: $actor,
            fromStatus: $fromStatus,
            toStatus: $next->value,
            reason: $reason,
            note: $note,
        );

        if ($next === ShipmentStatus::Delivered && ! in_array($order->status, [OrderStatus::Fulfilled->value, OrderStatus::Closed->value, OrderStatus::Cancelled->value], true)) {
            $originalStatus = $order->status;

            $order->update([
                'status' => OrderStatus::Fulfilled->value,
            ]);

            $this->recordHistory(
                order: $order,
                shipment: $shipment,
                domain: FulfillmentStatusDomain::Order,
                actor: $actor,
                fromStatus: $originalStatus,
                toStatus: OrderStatus::Fulfilled->value,
                reason: 'shipment_delivered',
                note: 'Order fulfilled automatically after shipment delivery.',
            );
        }
    }

    public function updateShipmentTracking(
        Order $order,
        Shipment $shipment,
        User $actor,
        string $carrier,
        string $trackingNumber,
        ?string $serviceLevel = null,
    ): void {
        if ($actor->role !== 'admin' || $shipment->order_id !== $order->id) {
            throw new InvalidArgumentException('The requested shipment tracking update is not allowed.');
        }

        $shipment->update([
            'carrier' => $carrier,
            'service_level' => $serviceLevel,
            'tracking_number' => $trackingNumber,
        ]);

        $this->recordHistory(
            order: $order,
            shipment: $shipment,
            domain: FulfillmentStatusDomain::Shipment,
            actor: $actor,
            fromStatus: $shipment->status,
            toStatus: $shipment->status,
            reason: 'tracking_updated',
            note: sprintf('Tracking number %s assigned to %s.', $trackingNumber, $carrier),
        );
    }

    private function shipmentHasReachedDispatch(Order $order): bool
    {
        return $order->shipments()
            ->whereIn('status', [
                ShipmentStatus::Dispatched->value,
                ShipmentStatus::InTransit->value,
                ShipmentStatus::Delivered->value,
                ShipmentStatus::DeliveryFailed->value,
                ShipmentStatus::ReturnToSender->value,
                ShipmentStatus::Returned->value,
            ])->exists();
    }

    private function shipmentHasTracking(Shipment $shipment): bool
    {
        return is_string($shipment->carrier)
            && $shipment->carrier !== ''
            && is_string($shipment->tracking_number)
            && $shipment->tracking_number !== '';
    }

    private function resolveOrderStatus(string $status): OrderStatus
    {
        return OrderStatus::from($status);
    }

    private function resolvePaymentStatus(string $status): PaymentStatus
    {
        return PaymentStatus::from($status);
    }

    private function resolveShipmentStatus(string $status): ShipmentStatus
    {
        return ShipmentStatus::from($status);
    }

    private function recordHistory(
        Order $order,
        FulfillmentStatusDomain $domain,
        User $actor,
        ?string $fromStatus,
        string $toStatus,
        ?string $reason = null,
        ?string $note = null,
        ?Payment $payment = null,
        ?Shipment $shipment = null,
    ): void {
        FulfillmentStatusHistory::query()->create([
            'order_id' => $order->id,
            'payment_id' => $payment?->id,
            'shipment_id' => $shipment?->id,
            'domain' => $domain->value,
            'from_status' => $fromStatus,
            'to_status' => $toStatus,
            'actor_id' => $actor->id,
            'actor_role' => $actor->role,
            'reason' => $reason,
            'note' => $note,
        ]);
    }
}
