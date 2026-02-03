<?php

namespace App\Models;

use Database\Factories\DisputeFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Dispute extends Model
{
    /** @use HasFactory<DisputeFactory> */
    use HasFactory;

    /**
     * @var list<string>
     */
    protected $fillable = [
        'order_id',
        'order_item_id',
        'opened_by_user_id',
        'status',
        'reason',
        'resolution',
        'refund_amount',
        'handled_by',
    ];

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function orderItem(): BelongsTo
    {
        return $this->belongsTo(OrderItem::class);
    }

    public function openedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'opened_by_user_id');
    }

    public function handledBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'handled_by');
    }
}
