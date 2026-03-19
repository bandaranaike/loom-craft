<?php

namespace App\Actions\Order;

use App\DTOs\Order\OrderAddressSummary;
use App\DTOs\Order\OrderItemSummary;
use App\DTOs\Order\OrderShowData;
use App\DTOs\Order\OrderSummaryResult;
use App\Models\Order;
use App\Models\OrderAddress;
use App\Models\OrderItem;
use App\Models\Payment;
use App\ValueObjects\Money;
use Illuminate\Support\Facades\Storage;

class ShowOrder
{
    public function __construct(
        private BuildOrderProgress $buildOrderProgress,
    ) {}

    public function handle(OrderShowData $data): OrderSummaryResult
    {
        $order = $data->order->load(['items.product.vendor', 'addresses', 'payment']);

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

        $payment = $order->payment;

        if ($payment === null) {
            throw new \RuntimeException('Order payment is missing.');
        }

        return new OrderSummaryResult(
            $order->id,
            $order->public_id,
            $order->status,
            $order->currency,
            Money::fromString((string) $order->subtotal)->amount,
            Money::fromString((string) $order->commission_total)->amount,
            Money::fromString((string) $order->total)->amount,
            $order->shipping_responsibility,
            $order->placed_at?->toDateTimeString(),
            $payment->method,
            $payment->status,
            Money::fromString((string) $payment->amount)->amount,
            $payment->currency,
            Money::fromString((string) $payment->original_amount)->amount,
            $payment->original_currency,
            $items,
            $addresses,
            $this->paymentProof($payment),
            $this->buildOrderProgress->handle($order->status, $payment->status),
            $this->canUploadPaymentProof($data->user, $order, $data->guestOrderId),
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

    private function canUploadPaymentProof(?\App\Models\User $user, Order $order, ?int $guestOrderId): bool
    {
        if ($order->payment?->method !== 'bank_transfer') {
            return false;
        }

        if ($user?->role === 'admin') {
            return true;
        }

        if ($user !== null) {
            return $order->user_id === $user->id;
        }

        return $guestOrderId === $order->id;
    }
}
