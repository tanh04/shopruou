<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * @property int $id
 * @property int $order_id
 * @property int $product_id
 * @property int $quantity
 * @property string $price
 * @property string|null $created_at
 * @property string|null $updated_at
 * @property-read \App\Models\Order $order
 * @property-read \App\Models\Product $product
 * @method static \Illuminate\Database\Eloquent\Builder<static>|OrderItems newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|OrderItems newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|OrderItems query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|OrderItems whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|OrderItems whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|OrderItems whereOrderId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|OrderItems wherePrice($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|OrderItems whereProductId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|OrderItems whereQuantity($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|OrderItems whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class OrderItems extends Model
{
    // Tên bảng (nếu Laravel không tự nhận diện đúng)
    protected $table = 'order_items';

    public $incrementing = true;
    protected $keyType = 'int';
    public $timestamps = false;
    // Khóa chính
    protected $primaryKey = 'id';

    // Cho phép fillable (dữ liệu được gán hàng loạt)
    protected $fillable = [
        'order_id',
        'product_id',
        'quantity',
        'price'
    ];

    // Quan hệ: Một OrderItem thuộc về một Order
    public function order()
    {
        return $this->belongsTo(Order::class, 'order_id', 'order_id');
    }

    // Quan hệ: Một OrderItem thuộc về một Product
    public function product()
    {
        return $this->belongsTo(Product::class, 'product_id', 'product_id');
    }
}
