<?php

namespace App\Http\Resources\Api\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class VendorOrderListResource extends JsonResource
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
            'vendor_items_total' => (float) $this->vendor_items_total,
            'items_count' => (int) $this->vendor_items_count,
            'created_at' => ($this->placed_at ?? $this->created_at)?->toISOString(),
        ];
    }
}
