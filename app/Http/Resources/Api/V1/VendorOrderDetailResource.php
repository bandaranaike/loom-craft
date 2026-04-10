<?php

namespace App\Http\Resources\Api\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Storage;

class VendorOrderDetailResource extends JsonResource
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
            'total' => (float) $this->total,
            'created_at' => $this->formatMobileDate($this->created_at),
            'items' => $this->items
                ->map(fn ($item): array => [
                    'id' => $item->id,
                    'product_id' => $item->product_id,
                    'product_name' => $item->product?->name,
                    'product_code' => $item->product?->product_code,
                    'quantity' => $item->quantity,
                    'unit_price' => (float) $item->unit_price,
                    'line_total' => (float) $item->line_total,
                    'status' => $this->status,
                    'currency' => $this->currency,
                    'image_url' => $this->resolvePrimaryImageUrl($item->product?->media),
                    'product_media' => $this->transformProductMedia($item->product?->media),
                ])
                ->values()
                ->all(),
        ];
    }

    private function formatMobileDate(mixed $date): ?string
    {
        return $date?->format('M d, Y g:i A');
    }

    private function resolvePrimaryImageUrl(mixed $media): ?string
    {
        $primaryImage = $media?->firstWhere('type', 'image');

        if (! is_string($primaryImage?->path) || $primaryImage->path === '') {
            return null;
        }

        return url(Storage::disk('public')->url($primaryImage->path));
    }

    /**
     * @return list<array<string, mixed>>
     */
    private function transformProductMedia(mixed $media): array
    {
        return $media
            ?->map(fn ($item): array => [
                'id' => $item->id,
                'type' => $item->type,
                'path' => $item->path,
                'media_url' => is_string($item->path) && $item->path !== ''
                    ? url(Storage::disk('public')->url($item->path))
                    : null,
                'thumbnail_url' => null,
                'alt_text' => $item->alt_text,
                'sort_order' => $item->sort_order,
            ])
            ->values()
            ->all() ?? [];
    }
}
