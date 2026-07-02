<?php

namespace App\Services;

use App\Enums\OrderStatus;
use App\Enums\ShipmentStatus;
use App\Models\Order;
use App\Models\OrderAddress;
use App\Notifications\OrderCustomerNotification;
use Illuminate\Notifications\AnonymousNotifiable;
use Illuminate\Support\Facades\Notification;

class OrderCustomerNotifier
{
    public function orderPlaced(Order $order): void
    {
        $this->notify(
            order: $order,
            event: 'order_placed',
            subject: 'Your LoomCraft order was placed',
            message: 'We have received your order and will keep you updated as it moves through fulfillment.',
        );
    }

    public function orderStatusChanged(Order $order, string $status): void
    {
        $status = OrderStatus::tryFrom($status);

        match ($status) {
            OrderStatus::Confirmed => $this->notify(
                order: $order,
                event: 'order_confirmed',
                subject: 'Your LoomCraft order is confirmed',
                message: 'Your order has been confirmed and is now being prepared.',
            ),
            OrderStatus::Cancelled => $this->notify(
                order: $order,
                event: 'order_cancelled',
                subject: 'Your LoomCraft order was cancelled',
                message: 'Your order has been cancelled. Contact support if you need more details.',
            ),
            default => null,
        };
    }

    public function shipmentStatusChanged(Order $order, string $status): void
    {
        $status = ShipmentStatus::tryFrom($status);

        match ($status) {
            ShipmentStatus::Dispatched => $this->notify(
                order: $order,
                event: 'order_dispatched',
                subject: 'Your LoomCraft order has shipped',
                message: 'Your order has been dispatched and is on its way.',
            ),
            ShipmentStatus::Delivered => $this->notify(
                order: $order,
                event: 'order_delivered',
                subject: 'Your LoomCraft order was delivered',
                message: 'Your order has been marked as delivered.',
            ),
            default => null,
        };
    }

    private function notify(Order $order, string $event, string $subject, string $message): void
    {
        $order = $order->fresh(['user', 'addresses']) ?? $order;
        $email = $order->user?->email ?? $order->guest_email;
        $phone = $order->user?->phone ?? $this->shippingAddress($order)?->phone;

        if ((! is_string($email) || $email === '') && (! is_string($phone) || $phone === '')) {
            return;
        }

        $notifiable = new AnonymousNotifiable;

        if (is_string($email) && $email !== '') {
            $notifiable->route('mail', $email);
        }

        if (is_string($phone) && $phone !== '') {
            $notifiable->route('dialog_esms', $phone);
        }

        Notification::send($notifiable, new OrderCustomerNotification(
            orderNumber: $order->order_number ?? sprintf('Order #%d', $order->id),
            event: $event,
            subject: $subject,
            message: $message,
            actionUrl: route('orders.show', ['order' => $order->public_id]),
        ));
    }

    private function shippingAddress(Order $order): ?OrderAddress
    {
        return $order->addresses->firstWhere('type', 'shipping');
    }
}
