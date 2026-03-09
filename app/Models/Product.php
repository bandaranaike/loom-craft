<?php

namespace App\Models;

use Database\Factories\ProductFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Product extends Model
{
    /** @use HasFactory<ProductFactory> */
    use HasFactory;

    /**
     * @var list<string>
     */
    protected $fillable = [
        'vendor_id',
        'name',
        'description',
        'vendor_price',
        'commission_rate',
        'selling_price',
        'materials',
        'pieces_count',
        'production_time_days',
        'dimension_length',
        'dimension_width',
        'dimension_height',
        'dimension_unit',
        'status',
    ];

    public function vendor(): BelongsTo
    {
        return $this->belongsTo(Vendor::class);
    }

    public function media(): HasMany
    {
        return $this->hasMany(ProductMedia::class);
    }

    public function cartItems(): HasMany
    {
        return $this->hasMany(CartItem::class);
    }

    public function orderItems(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    public function reports(): HasMany
    {
        return $this->hasMany(ProductReport::class);
    }

    public function categories(): BelongsToMany
    {
        return $this->belongsToMany(
            ProductCategory::class,
            'category_product',
            'product_id',
            'product_category_id',
        );
    }

    public function colors(): BelongsToMany
    {
        return $this->belongsToMany(
            ProductColor::class,
            'product_color_product',
            'product_id',
            'product_color_id',
        );
    }
}
