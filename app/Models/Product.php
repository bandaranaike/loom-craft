<?php

namespace App\Models;

use App\Services\ProductSlugGenerator;
use Database\Factories\ProductFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\App;

class Product extends Model
{
    /** @use HasFactory<ProductFactory> */
    use HasFactory;

    /**
     * @var list<string>
     */
    protected $fillable = [
        'vendor_id',
        'product_code',
        'slug',
        'name',
        'description',
        'vendor_price',
        'commission_rate',
        'selling_price',
        'discount_percentage',
        'materials',
        'pieces_count',
        'production_time_days',
        'dimension_length',
        'dimension_width',
        'dimension_height',
        'dimension_unit',
        'status',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'discount_percentage' => 'decimal:2',
        ];
    }

    protected static function booted(): void
    {
        static::saving(function (self $product): void {
            if (
                ($product->slug === null || $product->slug === '')
                || $product->isDirty('name')
            ) {
                $product->slug = App::make(ProductSlugGenerator::class)
                    ->generate($product->name, $product->exists ? $product->id : null);
            }
        });
    }

    public function resolveProductCode(): string
    {
        if (is_string($this->product_code) && $this->product_code !== '') {
            return $this->product_code;
        }

        return sprintf('PRD-%06d', (int) $this->id);
    }

    public function resolveSlug(): string
    {
        if (is_string($this->slug) && $this->slug !== '') {
            return $this->slug;
        }

        return App::make(ProductSlugGenerator::class)
            ->generate($this->name, $this->exists ? $this->id : null);
    }

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

    public function reviews(): HasMany
    {
        return $this->hasMany(ProductReview::class);
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

    public function hasDeliveredPurchaseBy(User $user): bool
    {
        return $this->orderItems()
            ->whereHas('order', function ($query) use ($user): void {
                $query
                    ->where('user_id', $user->id)
                    ->where('status', 'delivered');
            })
            ->exists();
    }

    public function hasReviewBy(User $user): bool
    {
        return $this->reviews()
            ->where('user_id', $user->id)
            ->exists();
    }

    public function canBeReviewedBy(User $user): bool
    {
        return $this->hasDeliveredPurchaseBy($user) && ! $this->hasReviewBy($user);
    }
}
