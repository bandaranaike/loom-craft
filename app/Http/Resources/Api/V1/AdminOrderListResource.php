<?php

namespace App\Http\Resources\Api\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AdminOrderListResource extends JsonResource
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
            'total' => (float) $this->total,
            'customer_name' => $this->user?->name ?? $this->guest_name,
            'items_count' => (int) $this->items_count,
            'created_at' => $this->formatMobileDate($this->created_at),
        ];
    }

    private function formatMobileDate(mixed $date): ?string
    {
        return $date?->format('M d, Y g:i A');
    }
}
