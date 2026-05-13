<?php

namespace App\Models;

use Database\Factories\ShippingCarrierFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ShippingCarrier extends Model
{
    /** @use HasFactory<ShippingCarrierFactory> */
    use HasFactory;

    /**
     * @var list<string>
     */
    protected $fillable = [
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

    public function services(): HasMany
    {
        return $this->hasMany(ShippingService::class);
    }

    public function shipments(): HasMany
    {
        return $this->hasMany(Shipment::class);
    }
}
