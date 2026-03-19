<?php

namespace App\Actions\Order;

use App\DTOs\Order\AdminOrderSummaryResult;
use App\DTOs\Order\OrderAddressSummary;
use App\DTOs\Order\OrderItemSummary;
use App\Models\Order;
use App\Models\OrderAddress;
use App\Models\OrderItem;
use App\Models\Payment;
use App\ValueObjects\Money;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Storage;

class ShowAdminOrder
{
    public function handle(int $orderId): AdminOrderSummaryResult
    {
        Gate::authorize('viewAny', Order::class);

        $order = Order::query()
            ->with(['items.product.vendor', 'addresses', 'payment', 'user'])
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

        return new AdminOrderSummaryResult(
            $order->id,
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
            $this->paymentProof($payment),
            ['paid', 'failed'],
            ['pending', 'paid', 'confirmed', 'delivered', 'cancelled'],
            in_array($payment->method, ['bank_transfer', 'cod'], true),
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
}
