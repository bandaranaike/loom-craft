<?php

namespace App\Actions\Order;

class BuildOrderProgress
{
    /**
     * @return array{
     *     is_cancelled: bool,
     *     summary: array{title: string, description: string}|null,
     *     steps: list<array{key: string, label: string, state: string}>
     * }
     */
    public function handle(string $orderStatus, string $paymentStatus): array
    {
        if ($orderStatus === 'cancelled') {
            return [
                'is_cancelled' => true,
                'summary' => [
                    'title' => 'Order cancelled',
                    'description' => $paymentStatus === 'paid'
                        ? 'Payment was recorded before this order was cancelled.'
                        : 'This order was cancelled before it was completed.',
                ],
                'steps' => [],
            ];
        }

        $paymentState = match (true) {
            $paymentStatus === 'paid' && in_array($orderStatus, ['confirmed', 'delivered'], true) => 'complete',
            in_array($paymentStatus, ['paid', 'pending', 'failed'], true) => 'current',
            default => 'upcoming',
        };

        $confirmedState = match ($orderStatus) {
            'confirmed' => 'current',
            'delivered' => 'complete',
            default => 'upcoming',
        };

        $deliveredState = $orderStatus === 'delivered' ? 'current' : 'upcoming';

        return [
            'is_cancelled' => false,
            'summary' => null,
            'steps' => [
                [
                    'key' => 'placed',
                    'label' => 'Order placed',
                    'state' => 'complete',
                ],
                [
                    'key' => 'payment',
                    'label' => $paymentStatus === 'failed' ? 'Payment failed' : 'Payment done',
                    'state' => $paymentState,
                ],
                [
                    'key' => 'confirmed',
                    'label' => 'Order confirmed',
                    'state' => $confirmedState,
                ],
                [
                    'key' => 'delivered',
                    'label' => 'Order delivered',
                    'state' => $deliveredState,
                ],
            ],
        ];
    }
}
