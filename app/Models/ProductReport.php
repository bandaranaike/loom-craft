<?php

namespace App\Models;

use Database\Factories\ProductReportFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProductReport extends Model
{
    /** @use HasFactory<ProductReportFactory> */
    use HasFactory;

    /**
     * @var list<string>
     */
    protected $fillable = [
        'product_id',
        'user_id',
        'guest_email',
        'reason',
        'status',
        'handled_by',
    ];

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function handledBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'handled_by');
    }
}
