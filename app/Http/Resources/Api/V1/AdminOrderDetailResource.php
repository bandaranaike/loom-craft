<?php

namespace App\Http\Resources\Api\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AdminOrderDetailResource extends JsonResource
{
    public static $wrap = null;

    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'public_id' => $this->public_id,
            'status' => $this->status,
            'currency' => $this->currency,
            'subtotal' => (float) $this->subtotal,
            'commission_total' => (float) $this->commission_total,
            'total' => (float) $this->total,
            'shipping_responsibility' => $this->shipping_responsibility,
            'placed_at' => $this->placed_at?->toISOString(),
            'customer' => [
                'name' => $this->user?->name ?? $this->guest_name,
                'email' => $this->user?->email ?? $this->guest_email,
            ],
            'payment' => [
                'method' => $this->payment?->method,
                'status' => $this->payment?->status,
                'amount' => $this->payment ? (float) $this->payment->amount : null,
                'currency' => $this->payment?->currency,
                'provider_reference' => $this->payment?->provider_reference,
            ],
            'addresses' => $this->addresses
                ->map(fn ($address): array => [
                    'type' => $address->type,
                    'full_name' => $address->full_name,
                    'line1' => $address->line1,
                    'line2' => $address->line2,
                    'city' => $address->city,
                    'region' => $address->region,
                    'postal_code' => $address->postal_code,
                    'country_code' => $address->country_code,
                    'phone' => $address->phone,
                ])
                ->values()
                ->all(),
            'shipments' => $this->shipments
                ->map(fn ($shipment): array => [
                    'id' => $shipment->id,
                    'vendor_id' => $shipment->vendor_id,
                    'responsibility' => $shipment->responsibility,
                    'status' => $shipment->status,
                    'carrier' => $shipment->carrier,
                    'tracking_number' => $shipment->tracking_number,
                    'shipped_at' => $shipment->shipped_at?->toISOString(),
                    'delivered_at' => $shipment->delivered_at?->toISOString(),
                ])
                ->values()
                ->all(),
            'items' => $this->items
                ->map(fn ($item): array => [
                    'id' => $item->id,
                    'product_id' => $item->product_id,
                    'product_name' => $item->product?->name,
                    'product_code' => $item->product?->product_code,
                    'vendor_id' => $item->vendor_id,
                    'vendor_name' => $item->vendor?->display_name,
                    'quantity' => $item->quantity,
                    'unit_price' => (float) $item->unit_price,
                    'line_total' => (float) $item->line_total,
                ])
                ->values()
                ->all(),
        ];
    }
}
