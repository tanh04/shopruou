<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\ProductImage;
/**
 * @property int $product_id
 * @property int $category_id
 * @property int $brand_id
 * @property string $product_name
 * @property string $product_description
 * @property float $product_price
 * @property float|null $product_capacity
 * @property int $product_stock
 * @property string $product_image
 * @property int $product_status
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\Brand $brand
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\CartItems> $cartItems
 * @property-read int|null $cart_items_count
 * @property-read \App\Models\Category $category
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\OrderItems> $orderItems
 * @property-read int|null $order_items_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Review> $reviews
 * @property-read int|null $reviews_count
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Product newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Product newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Product query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Product whereBrandId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Product whereCategoryId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Product whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Product whereProductCapacity($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Product whereProductDescription($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Product whereProductId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Product whereProductImage($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Product whereProductName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Product whereProductPrice($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Product whereProductStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Product whereProductStock($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Product whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class Product extends Model
{
    protected $table = 'products';
    protected $primaryKey = 'product_id';
    public $incrementing = true;
    protected $keyType = 'int';
    public $timestamps = true;

    protected $fillable = [
        'product_name',
        'product_description',
        'alcohol_percent',
        'grape_variety',
        'cost_price',
        'product_price',
        'product_capacity',
        'product_stock',
        'category_id',
        'brand_id',
        'product_image',
        'product_status', 
        'promo_price','promo_start','promo_end'
    ];

    public function getRouteKeyName()
    {
        return 'product_id'; // <<< để {product} bind theo product_id
    }

    protected $casts = [
        'promo_start' => 'datetime',
        'promo_end'   => 'datetime',
    ];

    public function getEffectivePriceAttribute()
    {
        $start = $this->promo_start;
        $end   = $this->promo_end?->endOfDay();

        $isPromo = !is_null($this->promo_price)
            && $this->promo_price > 0
            && $this->promo_price < $this->product_price
            && (!$start || $start->lte(now()))
            && (!$end   || $end->gte(now()));

        return $isPromo ? $this->promo_price : $this->product_price;
    }

    public function getCurrentPriceAttribute()
    {
        return $this->is_promo_active ? $this->promo_price : $this->product_price;
    }

    public function getDiscountPercentAttribute(): ?int
    {
        if (!$this->is_promo_active) return null;
        if (!$this->product_price || $this->product_price <= 0) return null;
        return (int) round(100 * (1 - ($this->promo_price / $this->product_price)));
    }

    public function images()
    {
        return $this->hasMany(ProductImage::class, 'product_id', 'product_id')
                    ->orderBy('sort_order')->orderBy('id');
    }
    public function category()
    {
        return $this->belongsTo(Category::class, 'category_id', 'category_id');
    }

    public function brand()
    {
        return $this->belongsTo(Brand::class, 'brand_id', 'brand_id');
    }

    public function cartItems()
    {
        return $this->hasMany(CartItems::class, 'product_id', 'product_id');
    }

    public function orderItems()
    {
        return $this->hasMany(OrderItems::class, 'product_id', 'product_id');
    }
    public function reviews()
    {
        // Khớp FK ở reviews.product_id với PK ở products.product_id
        return $this->hasMany(Review::class, 'product_id', 'product_id')
                    ->where('status', 1)
                    ->latest();
    }
}
