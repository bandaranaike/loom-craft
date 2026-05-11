<?php

namespace App\Services\Fulfillment;

use App\Models\Order;
use App\Models\OrderAddress;
use App\Models\OrderItem;
use App\Models\Shipment;

class ShipmentLabelDataBuilder
{
    /**
     * @return array<string, mixed>
     */
    public function build(Order $order, Shipment $shipment): array
    {
        if ($shipment->order_id !== $order->id) {
            abort(404);
        }

        $order->loadMissing(['addresses', 'invoice', 'items.product', 'items.vendor', 'user']);
        $shipment->loadMissing(['vendor']);

        $shippingAddress = $order->addresses->firstWhere('type', 'shipping');
        $primaryItem = $order->items->first();

        return [
            'document_title' => sprintf('Shipment Label %s', $shipment->shipment_number ?? $shipment->id),
            'order_number' => $order->order_number ?? $order->public_id ?? sprintf('Order #%d', $order->id),
            'public_id' => $order->public_id,
            'invoice_number' => $order->invoice?->invoice_number ?? 'Pending',
            'shipment_number' => $shipment->shipment_number ?? sprintf('Shipment #%d', $shipment->id),
            'tracking_number' => $shipment->tracking_number ?? 'Pending',
            'carrier' => $shipment->carrier ?? 'Pending',
            'service_level' => $shipment->service_level ?? 'Standard',
            'order_date' => $order->placed_at?->format('d M Y') ?? $order->created_at?->format('d M Y'),
            'ship_to' => $this->shippingAddress($order, $shippingAddress),
            'return_to' => [
                'name' => 'LoomCraft Fulfillment Center',
                'lines' => ['Colombo 05', 'Sri Lanka'],
                'phone' => null,
            ],
            'parcel' => $this->parcel($shipment),
            'product' => $this->primaryProduct($primaryItem, $order->items->count()),
            'products' => $order->items->map(fn (OrderItem $item): array => [
                'name' => $item->product?->name ?? 'Product',
                'code' => $item->product?->product_code,
                'quantity' => $item->quantity,
                'vendor' => $item->vendor?->display_name,
            ])->values()->all(),
            'print_generated_at' => now()->format('d M Y H:i'),
        ];
    }

    /**
     * @return array{name: string, lines: list<string>, phone: ?string}
     */
    private function shippingAddress(Order $order, ?OrderAddress $address): array
    {
        return [
            'name' => $address?->full_name ?? $order->user?->name ?? $order->guest_name ?? 'Customer',
            'lines' => array_values(array_filter([
                $address?->line1,
                $address?->line2,
                trim(implode(', ', array_filter([$address?->city, $address?->region]))),
                trim(implode(' ', array_filter([$address?->postal_code, $address?->country_code]))),
            ], static fn (?string $line): bool => is_string($line) && $line !== '')),
            'phone' => $address?->phone,
        ];
    }

    /**
     * @return array<string, string>
     */
    private function parcel(Shipment $shipment): array
    {
        $weight = $shipment->parcel_weight === null
            ? 'Pending'
            : sprintf('%s %s', $shipment->parcel_weight, $shipment->weight_unit ?? 'kg');

        $dimensions = collect([$shipment->parcel_length, $shipment->parcel_width, $shipment->parcel_height])
            ->filter(fn (mixed $value): bool => $value !== null)
            ->map(fn (mixed $value): string => (string) $value)
            ->implode(' x ');

        return [
            'package_count' => (string) ($shipment->package_count ?? 1),
            'weight' => $weight,
            'dimensions' => $dimensions === '' ? 'Pending' : sprintf('%s %s', $dimensions, $shipment->parcel_dimension_unit ?? 'cm'),
        ];
    }

    /**
     * @return array<string, int|string|null>
     */
    private function primaryProduct(?OrderItem $item, int $itemCount): array
    {
        $product = $item?->product;

        $dimensions = collect([$product?->dimension_length, $product?->dimension_width, $product?->dimension_height])
            ->filter(fn (mixed $value): bool => $value !== null)
            ->map(fn (mixed $value): string => (string) $value)
            ->implode(' x ');

        return [
            'name' => $product?->name ?? 'LoomCraft Product',
            'code' => $product?->product_code,
            'quantity' => $item?->quantity ?? 0,
            'item_count' => $itemCount,
            'dimensions' => $dimensions === '' ? null : sprintf('%s %s', $dimensions, $product?->dimension_unit ?? 'cm'),
            'vendor' => $item?->vendor?->display_name,
        ];
    }
}
