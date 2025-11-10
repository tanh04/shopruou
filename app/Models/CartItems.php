<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * @property int $id
 * @property int $cart_id
 * @property int $product_id
 * @property int $quantity
 * @property float $price
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\Cart $cart
 * @property-read \App\Models\Product $product
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CartItems newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CartItems newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CartItems query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CartItems whereCartId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CartItems whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CartItems whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CartItems wherePrice($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CartItems whereProductId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CartItems whereQuantity($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CartItems whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class CartItems extends Model
{
    protected $table = 'cart_items';  // Đặt tên bảng đúng

    protected $fillable = [
        'cart_id',
        'product_id',
        'quantity',
        'price',
    ];
    // Mỗi item thuộc về 1 product
    public function product()
    {
        return $this->belongsTo(Product::class, 'product_id', 'product_id');
    }

    // Optional: liên kết ngược về cart
    public function cart()
    {
        return $this->belongsTo(Cart::class, 'cart_id', 'cart_id');
    }
}
