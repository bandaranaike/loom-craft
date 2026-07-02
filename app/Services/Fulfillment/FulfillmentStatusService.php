<?php

namespace App\Services\Fulfillment;

use App\Enums\ComplaintCategory;
use App\Enums\ComplaintStatus;
use App\Enums\FulfillmentStatusDomain;
use App\Enums\OrderReturnStatus;
use App\Enums\OrderStatus;
use App\Enums\PaymentStatus;
use App\Enums\ShipmentExceptionReason;
use App\Enums\ShipmentStatus;
use App\Models\Complaint;
use App\Models\FulfillmentStatusHistory;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\OrderReturn;
use App\Models\Payment;
use App\Models\Shipment;
use App\Models\ShippingCarrier;
use App\Models\ShippingService;
use App\Models\User;
use App\Services\OrderCustomerNotifier;
use Carbon\CarbonInterface;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

class FulfillmentStatusService
{
    public function __construct(
        private readonly OrderCustomerNotifier $orderCustomerNotifier,
    ) {}

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
    public function returnStatusOptions(): array
    {
        return OrderReturnStatus::values();
    }

    /**
     * @return list<string>
     */
    public function complaintStatusOptions(): array
    {
        return ComplaintStatus::values();
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

    public function canTransitionReturn(Order $order, OrderReturn $orderReturn, string $nextStatus, User $actor): bool
    {
        if ($actor->role !== 'admin' || $orderReturn->order_id !== $order->id) {
            return false;
        }

        $currentStatus = $this->resolveReturnStatus($orderReturn->status);
        $next = $this->resolveReturnStatus($nextStatus);

        if ($currentStatus === $next) {
            return true;
        }

        return match ($currentStatus) {
            OrderReturnStatus::Requested => in_array($next, [OrderReturnStatus::Approved, OrderReturnStatus::Rejected, OrderReturnStatus::Cancelled], true),
            OrderReturnStatus::Approved => in_array($next, [OrderReturnStatus::InTransit, OrderReturnStatus::Cancelled], true),
            OrderReturnStatus::InTransit => $next === OrderReturnStatus::ReceivedByAdmin,
            OrderReturnStatus::ReceivedByAdmin => $next === OrderReturnStatus::Inspected,
            OrderReturnStatus::Inspected => in_array($next, [OrderReturnStatus::VendorReview, OrderReturnStatus::Resolved], true),
            OrderReturnStatus::VendorReview => $next === OrderReturnStatus::Resolved,
            OrderReturnStatus::Resolved => $next === OrderReturnStatus::Closed,
            OrderReturnStatus::Rejected, OrderReturnStatus::Closed, OrderReturnStatus::Cancelled => false,
        };
    }

    public function canTransitionComplaint(Complaint $complaint, string $nextStatus, User $actor): bool
    {
        if ($actor->role !== 'admin') {
            return false;
        }

        $currentStatus = $this->resolveComplaintStatus($complaint->status);
        $next = $this->resolveComplaintStatus($nextStatus);

        if ($currentStatus === $next) {
            return true;
        }

        return match ($currentStatus) {
            ComplaintStatus::Open => in_array($next, [
                ComplaintStatus::InReview,
                ComplaintStatus::WaitingForCustomer,
                ComplaintStatus::WaitingForVendor,
                ComplaintStatus::WaitingForCourier,
                ComplaintStatus::Resolved,
                ComplaintStatus::Cancelled,
            ], true),
            ComplaintStatus::InReview => in_array($next, [
                ComplaintStatus::WaitingForCustomer,
                ComplaintStatus::WaitingForVendor,
                ComplaintStatus::WaitingForCourier,
                ComplaintStatus::Resolved,
                ComplaintStatus::Cancelled,
            ], true),
            ComplaintStatus::WaitingForCustomer, ComplaintStatus::WaitingForVendor, ComplaintStatus::WaitingForCourier => in_array($next, [
                ComplaintStatus::InReview,
                ComplaintStatus::Resolved,
                ComplaintStatus::Cancelled,
            ], true),
            ComplaintStatus::Resolved => $next === ComplaintStatus::Closed,
            ComplaintStatus::Closed, ComplaintStatus::Cancelled => false,
        };
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

        $this->orderCustomerNotifier->orderStatusChanged($order, $next->value);
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

    /**
     * @param  list<array{order_item_id: int, quantity: int, condition?: string|null, resolution?: string|null, note?: string|null}>  $items
     */
    public function createReturnRequest(
        Order $order,
        User $actor,
        string $reason,
        array $items,
        ?Shipment $shipment = null,
        ?string $customerNote = null,
        ?string $adminNote = null,
    ): OrderReturn {
        if ($actor->role !== 'admin') {
            throw new InvalidArgumentException('The requested return creation is not allowed.');
        }

        if ($shipment !== null && $shipment->order_id !== $order->id) {
            throw new InvalidArgumentException('The selected shipment does not belong to the order.');
        }

        return DB::transaction(function () use ($order, $actor, $reason, $items, $shipment, $customerNote, $adminNote): OrderReturn {
            $orderReturn = $order->returns()->create([
                'shipment_id' => $shipment?->id,
                'requested_by' => $actor->id,
                'status' => OrderReturnStatus::Requested->value,
                'reason' => $reason,
                'customer_note' => $customerNote,
                'admin_note' => $adminNote,
                'requested_at' => now(),
            ]);

            foreach ($items as $item) {
                $orderItem = OrderItem::query()
                    ->where('order_id', $order->id)
                    ->findOrFail($item['order_item_id']);

                if ($item['quantity'] > $orderItem->quantity) {
                    throw new InvalidArgumentException('Return quantity cannot exceed the ordered quantity.');
                }

                $orderReturn->items()->create([
                    'order_item_id' => $orderItem->id,
                    'quantity' => $item['quantity'],
                    'condition' => $item['condition'] ?? null,
                    'resolution' => $item['resolution'] ?? null,
                    'note' => $item['note'] ?? null,
                ]);
            }

            $this->recordHistory(
                order: $order,
                orderReturn: $orderReturn,
                domain: FulfillmentStatusDomain::Return,
                actor: $actor,
                fromStatus: null,
                toStatus: OrderReturnStatus::Requested->value,
                reason: 'return_requested',
                note: $adminNote ?? $customerNote,
            );

            return $orderReturn;
        });
    }

    public function updateReturnStatus(
        Order $order,
        OrderReturn $orderReturn,
        string $nextStatus,
        User $actor,
        ?string $reason = null,
        ?string $note = null,
        ?string $resolution = null,
    ): void {
        $next = $this->resolveReturnStatus($nextStatus);

        if (! $this->canTransitionReturn($order, $orderReturn, $next->value, $actor)) {
            throw new InvalidArgumentException('The requested return status transition is not allowed.');
        }

        $fromStatus = $orderReturn->status;
        $updates = ['status' => $next->value];

        if ($note !== null) {
            $updates['admin_note'] = $note;
        }

        if ($resolution !== null) {
            $updates['resolution'] = $resolution;
        }

        if (in_array($next, [OrderReturnStatus::Approved, OrderReturnStatus::Rejected], true)) {
            $updates['reviewed_by'] = $actor->id;
        }

        if ($next === OrderReturnStatus::ReceivedByAdmin) {
            $updates['received_by'] = $actor->id;
        }

        $timestampColumn = match ($next) {
            OrderReturnStatus::Approved => 'approved_at',
            OrderReturnStatus::Rejected => 'rejected_at',
            OrderReturnStatus::InTransit => 'in_transit_at',
            OrderReturnStatus::ReceivedByAdmin => 'admin_received_at',
            OrderReturnStatus::Inspected => 'inspected_at',
            OrderReturnStatus::VendorReview => 'vendor_review_started_at',
            OrderReturnStatus::Resolved => 'resolved_at',
            OrderReturnStatus::Closed => 'closed_at',
            OrderReturnStatus::Cancelled => 'cancelled_at',
            OrderReturnStatus::Requested => null,
        };

        if ($timestampColumn !== null && $orderReturn->{$timestampColumn} === null) {
            $updates[$timestampColumn] = now();
        }

        $orderReturn->update($updates);

        $this->recordHistory(
            order: $order,
            orderReturn: $orderReturn,
            domain: FulfillmentStatusDomain::Return,
            actor: $actor,
            fromStatus: $fromStatus,
            toStatus: $next->value,
            reason: $reason,
            note: $note,
        );
    }

    public function updateReturnTracking(
        Order $order,
        OrderReturn $orderReturn,
        User $actor,
        int $shippingCarrierId,
        string $trackingNumber,
        ?int $shippingServiceId = null,
        array $parcelMetrics = [],
    ): void {
        if ($actor->role !== 'admin' || $orderReturn->order_id !== $order->id) {
            throw new InvalidArgumentException('The requested return tracking update is not allowed.');
        }

        $carrier = ShippingCarrier::query()->findOrFail($shippingCarrierId);
        $service = $shippingServiceId === null
            ? null
            : ShippingService::query()
                ->where('shipping_carrier_id', $carrier->id)
                ->findOrFail($shippingServiceId);

        $orderReturn->update([
            'shipping_carrier_id' => $carrier->id,
            'shipping_service_id' => $service?->id,
            'carrier' => $carrier->name,
            'service_level' => $service?->name,
            'tracking_number' => $trackingNumber,
            'package_count' => $parcelMetrics['package_count'] ?? $orderReturn->package_count,
            'parcel_weight' => $parcelMetrics['parcel_weight'] ?? $orderReturn->parcel_weight,
            'weight_unit' => $parcelMetrics['weight_unit'] ?? $orderReturn->weight_unit,
            'parcel_length' => $parcelMetrics['parcel_length'] ?? $orderReturn->parcel_length,
            'parcel_width' => $parcelMetrics['parcel_width'] ?? $orderReturn->parcel_width,
            'parcel_height' => $parcelMetrics['parcel_height'] ?? $orderReturn->parcel_height,
            'parcel_dimension_unit' => $parcelMetrics['parcel_dimension_unit'] ?? $orderReturn->parcel_dimension_unit,
        ]);

        $this->recordHistory(
            order: $order,
            orderReturn: $orderReturn,
            domain: FulfillmentStatusDomain::Return,
            actor: $actor,
            fromStatus: $orderReturn->status,
            toStatus: $orderReturn->status,
            reason: 'return_tracking_updated',
            note: sprintf('Return tracking number %s assigned to %s.', $trackingNumber, $carrier->name),
        );
    }

    /**
     * @param  array{user_id?: int|null, guest_email?: string|null, shipment_id?: int|null, order_return_id?: int|null, payment_id?: int|null, category: string, severity?: string|null, subject: string, message: string, assigned_to?: int|null, resolution_type?: string|null, courier_claim_reference?: string|null}  $data
     */
    public function createComplaint(Order $order, User $actor, array $data): Complaint
    {
        if ($actor->role !== 'admin') {
            throw new InvalidArgumentException('The requested complaint creation is not allowed.');
        }

        $category = ComplaintCategory::from($data['category']);

        return DB::transaction(function () use ($order, $actor, $data, $category): Complaint {
            $complaint = $order->complaints()->create([
                'user_id' => $data['user_id'] ?? $order->user_id,
                'guest_email' => $data['guest_email'] ?? $order->guest_email,
                'shipment_id' => $data['shipment_id'] ?? null,
                'order_return_id' => $data['order_return_id'] ?? null,
                'payment_id' => $data['payment_id'] ?? null,
                'category' => $category->value,
                'severity' => $data['severity'] ?? 'normal',
                'subject' => $data['subject'],
                'message' => $data['message'],
                'status' => ComplaintStatus::Open->value,
                'resolution_type' => $data['resolution_type'] ?? null,
                'courier_claim_reference' => $data['courier_claim_reference'] ?? null,
                'handled_by' => $actor->id,
                'assigned_to' => $data['assigned_to'] ?? $actor->id,
                'opened_at' => now(),
                'first_response_due_at' => now()->addDay(),
                'sla_due_at' => $this->complaintSlaDueAt($data['severity'] ?? 'normal'),
            ]);

            $this->recordHistory(
                order: $order,
                complaint: $complaint,
                domain: FulfillmentStatusDomain::Complaint,
                actor: $actor,
                fromStatus: null,
                toStatus: ComplaintStatus::Open->value,
                reason: 'complaint_opened',
                note: $data['subject'],
            );

            return $complaint;
        });
    }

    public function updateComplaintStatus(
        Complaint $complaint,
        string $nextStatus,
        User $actor,
        ?string $reason = null,
        ?string $note = null,
        ?string $resolutionType = null,
        ?string $resolutionNote = null,
        ?string $courierClaimReference = null,
    ): void {
        $next = $this->resolveComplaintStatus($nextStatus);

        if (! $this->canTransitionComplaint($complaint, $next->value, $actor)) {
            throw new InvalidArgumentException('The requested complaint status transition is not allowed.');
        }

        $fromStatus = $complaint->status;
        $updates = [
            'status' => $next->value,
            'handled_by' => $actor->id,
        ];

        if ($resolutionType !== null) {
            $updates['resolution_type'] = $resolutionType;
        }

        if ($resolutionNote !== null) {
            $updates['resolution_note'] = $resolutionNote;
        }

        if ($courierClaimReference !== null) {
            $updates['courier_claim_reference'] = $courierClaimReference;
        }

        if ($next !== ComplaintStatus::Open && $complaint->first_responded_at === null) {
            $updates['first_responded_at'] = now();
        }

        if ($next === ComplaintStatus::Resolved && $complaint->resolved_at === null) {
            $updates['resolved_at'] = now();
        }

        if ($next === ComplaintStatus::Closed && $complaint->closed_at === null) {
            $updates['closed_at'] = now();
        }

        $complaint->update($updates);

        $this->recordHistory(
            order: $complaint->order,
            complaint: $complaint,
            domain: FulfillmentStatusDomain::Complaint,
            actor: $actor,
            fromStatus: $fromStatus,
            toStatus: $next->value,
            reason: $reason,
            note: $note ?? $resolutionNote,
        );
    }

    public function updateShipmentStatus(
        Order $order,
        Shipment $shipment,
        string $nextStatus,
        User $actor,
        ?string $reason = null,
        ?string $note = null,
        ?string $deliveryExceptionReason = null,
        ?string $deliveryExceptionNote = null,
    ): void {
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

        if (in_array($next, [ShipmentStatus::DeliveryFailed, ShipmentStatus::ReturnToSender], true)) {
            if ($deliveryExceptionReason === null) {
                throw new InvalidArgumentException('A delivery exception reason is required for this shipment status.');
            }

            ShipmentExceptionReason::from($deliveryExceptionReason);

            $updates['delivery_exception_reason'] = $deliveryExceptionReason;
            $updates['delivery_exception_note'] = $deliveryExceptionNote;
            $updates['delivery_exception_at'] = now();
            $updates['failed_delivery_attempts'] = $next === ShipmentStatus::DeliveryFailed
                ? $shipment->failed_delivery_attempts + 1
                : $shipment->failed_delivery_attempts;
        }

        $shipment->update($updates);

        $this->recordHistory(
            order: $order,
            shipment: $shipment,
            domain: FulfillmentStatusDomain::Shipment,
            actor: $actor,
            fromStatus: $fromStatus,
            toStatus: $next->value,
            reason: $reason ?? ($deliveryExceptionReason === null ? null : 'delivery_exception'),
            note: $note ?? $deliveryExceptionNote,
        );

        $this->orderCustomerNotifier->shipmentStatusChanged($order, $next->value);

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
        int $shippingCarrierId,
        string $trackingNumber,
        ?int $shippingServiceId = null,
    ): void {
        if ($actor->role !== 'admin' || $shipment->order_id !== $order->id) {
            throw new InvalidArgumentException('The requested shipment tracking update is not allowed.');
        }

        $carrier = ShippingCarrier::query()->findOrFail($shippingCarrierId);
        $service = $shippingServiceId === null
            ? null
            : ShippingService::query()
                ->where('shipping_carrier_id', $carrier->id)
                ->findOrFail($shippingServiceId);

        $shipment->update([
            'shipping_carrier_id' => $carrier->id,
            'shipping_service_id' => $service?->id,
            'carrier' => $carrier->name,
            'service_level' => $service?->name,
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
            note: sprintf('Tracking number %s assigned to %s.', $trackingNumber, $carrier->name),
        );
    }

    /**
     * @param  array{recipient_name?: string|null, proof_reference?: string|null, evidence_path?: string|null, evidence_original_name?: string|null, evidence_mime_type?: string|null, note?: string|null}  $data
     */
    public function recordDeliveryEvidence(Order $order, Shipment $shipment, User $actor, array $data): void
    {
        if ($actor->role !== 'admin' || $shipment->order_id !== $order->id) {
            throw new InvalidArgumentException('The requested delivery evidence update is not allowed.');
        }

        $shipment->update([
            'delivery_recipient_name' => $data['recipient_name'] ?? null,
            'delivery_proof_reference' => $data['proof_reference'] ?? null,
            'delivery_evidence_path' => $data['evidence_path'] ?? $shipment->delivery_evidence_path,
            'delivery_evidence_original_name' => $data['evidence_original_name'] ?? $shipment->delivery_evidence_original_name,
            'delivery_evidence_mime_type' => $data['evidence_mime_type'] ?? $shipment->delivery_evidence_mime_type,
            'delivery_evidence_uploaded_at' => isset($data['evidence_path']) ? now() : $shipment->delivery_evidence_uploaded_at,
            'delivery_confirmed_by' => $actor->id,
            'delivery_note' => $data['note'] ?? null,
        ]);

        $this->recordHistory(
            order: $order,
            shipment: $shipment,
            domain: FulfillmentStatusDomain::Shipment,
            actor: $actor,
            fromStatus: $shipment->status,
            toStatus: $shipment->status,
            reason: 'delivery_evidence_recorded',
            note: $data['note'] ?? $data['proof_reference'] ?? null,
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
        return $shipment->shipping_carrier_id !== null
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

    private function resolveReturnStatus(string $status): OrderReturnStatus
    {
        return OrderReturnStatus::from($status);
    }

    private function resolveComplaintStatus(string $status): ComplaintStatus
    {
        return ComplaintStatus::from($status);
    }

    private function complaintSlaDueAt(string $severity): CarbonInterface
    {
        return match ($severity) {
            'urgent' => now()->addDay(),
            'high' => now()->addDays(2),
            'low' => now()->addDays(5),
            default => now()->addDays(3),
        };
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
        ?OrderReturn $orderReturn = null,
        ?Complaint $complaint = null,
    ): void {
        FulfillmentStatusHistory::query()->create([
            'order_id' => $order->id,
            'payment_id' => $payment?->id,
            'shipment_id' => $shipment?->id,
            'order_return_id' => $orderReturn?->id,
            'complaint_id' => $complaint?->id,
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
