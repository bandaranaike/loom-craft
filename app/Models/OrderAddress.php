<?php

namespace App\Models;

use Database\Factories\OrderAddressFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OrderAddress extends Model
{
    /** @use HasFactory<OrderAddressFactory> */
    use HasFactory;

    /**
     * @var list<string>
     */
    protected $fillable = [
        'order_id',
        'type',
        'full_name',
        'line1',
        'line2',
        'city',
        'region',
        'postal_code',
        'country_code',
        'phone',
    ];

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }
}
