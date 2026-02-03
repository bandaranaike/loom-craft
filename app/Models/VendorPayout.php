<?php

namespace App\Models;

use Database\Factories\VendorPayoutFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class VendorPayout extends Model
{
    /** @use HasFactory<VendorPayoutFactory> */
    use HasFactory;

    /**
     * @var list<string>
     */
    protected $fillable = [
        'vendor_id',
        'order_id',
        'amount',
        'currency',
        'status',
        'paid_at',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'paid_at' => 'datetime',
        ];
    }

    public function vendor(): BelongsTo
    {
        return $this->belongsTo(Vendor::class);
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }
}
