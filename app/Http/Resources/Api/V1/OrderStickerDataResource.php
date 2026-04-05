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

        return [
            'id' => $this->id,
            'public_id' => $this->public_id,
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
            'products' => $this->items
                ->map(fn ($item): array => [
                    'id' => $item->id,
                    'product_name' => $item->product?->name,
                    'product_code' => $item->product?->product_code,
                    'vendor_name' => $item->vendor?->display_name,
                    'quantity' => $item->quantity,
                ])
                ->values()
                ->all(),
        ];
    }
}
