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
            $paymentStatus === 'paid' => 'complete',
            in_array($paymentStatus, ['pending', 'failed'], true) => 'current',
            default => 'upcoming',
        };

        $confirmedState = match ($orderStatus) {
            'confirmed' => 'current',
            'shipped', 'delivered' => 'complete',
            default => 'upcoming',
        };

        $shippedState = match ($orderStatus) {
            'shipped' => 'current',
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
                    'key' => 'shipped',
                    'label' => 'Order shipped',
                    'state' => $shippedState,
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
