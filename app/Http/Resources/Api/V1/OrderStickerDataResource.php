<?php

namespace App\Http\Resources\Api\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class OrderStickerDataResource extends JsonResource
{
    public static $wrap = null;

    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $shippingAddress = $this->addresses->firstWhere('type', 'shipping');
        $shipment = $this->shipments->first();

        return [
            'id' => $this->id,
            'public_id' => $this->public_id,
            'order_number' => $this->order_number,
            'invoice_number' => $this->invoice?->invoice_number,
            'shipment_number' => $shipment?->shipment_number,
            'tracking_number' => $shipment?->tracking_number,
            'carrier' => $shipment?->carrier,
            'service_level' => $shipment?->service_level,
            'status' => $this->status,
            'customer_name' => $shippingAddress?->full_name ?? $this->user?->name ?? $this->guest_name,
            'customer_email' => $this->user?->email ?? $this->guest_email,
            'customer_phone' => $shippingAddress?->phone,
            'shipping_address' => [
                'line1' => $shippingAddress?->line1,
                'line2' => $shippingAddress?->line2,
                'city' => $shippingAddress?->city,
                'region' => $shippingAddress?->region,
                'postal_code' => $shippingAddress?->postal_code,
                'country_code' => $shippingAddress?->country_code,
            ],
            'parcel' => [
                'package_count' => $shipment?->package_count,
                'weight' => $shipment?->parcel_weight !== null ? (float) $shipment->parcel_weight : null,
                'weight_unit' => $shipment?->weight_unit,
                'length' => $shipment?->parcel_length !== null ? (float) $shipment->parcel_length : null,
                'width' => $shipment?->parcel_width !== null ? (float) $shipment->parcel_width : null,
                'height' => $shipment?->parcel_height !== null ? (float) $shipment->parcel_height : null,
                'dimension_unit' => $shipment?->parcel_dimension_unit,
            ],
            'products' => $this->items
                ->map(fn ($item): array => [
                    'id' => $item->id,
                    'product_name' => $item->product?->name,
                    'product_code' => $item->product?->product_code,
                    'vendor_name' => $item->vendor?->display_name,
                    'quantity' => $item->quantity,
                    'dimension_length' => $item->product?->dimension_length !== null ? (float) $item->product->dimension_length : null,
                    'dimension_width' => $item->product?->dimension_width !== null ? (float) $item->product->dimension_width : null,
                    'dimension_height' => $item->product?->dimension_height !== null ? (float) $item->product->dimension_height : null,
                    'dimension_unit' => $item->product?->dimension_unit,
                ])
                ->values()
                ->all(),
        ];
    }
}
