<?php

namespace App\Models;

use Database\Factories\ShippingServiceFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ShippingService extends Model
{
    /** @use HasFactory<ShippingServiceFactory> */
    use HasFactory;

    /**
     * @var list<string>
     */
    protected $fillable = [
        'shipping_carrier_id',
        'name',
        'code',
        'is_active',
        'sort_order',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
        ];
    }

    public function carrier(): BelongsTo
    {
        return $this->belongsTo(ShippingCarrier::class, 'shipping_carrier_id');
    }

    public function shipments(): HasMany
    {
        return $this->hasMany(Shipment::class);
    }
}
