<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * @property int $coupon_id
 * @property string $coupon_code
 * @property int $coupon_quantity
 * @property string|null $discount_percent
 * @property string|null $discount_amount
 * @property string $min_order_value
 * @property string $start_date
 * @property string $end_date
 * @property int $status
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Coupon newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Coupon newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Coupon query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Coupon whereCouponCode($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Coupon whereCouponId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Coupon whereCouponQuantity($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Coupon whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Coupon whereDiscountAmount($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Coupon whereDiscountPercent($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Coupon whereEndDate($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Coupon whereMinOrderValue($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Coupon whereStartDate($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Coupon whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Coupon whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class Coupon extends Model
{
    // Tên bảng
    protected $table = 'coupons';

    // Khóa chính
    protected $primaryKey = 'coupon_id';

    // Cho phép gán dữ liệu hàng loạt
    protected $fillable = [
        'coupon_code',
        'coupon_quantity',
        'start_date',
        'end_date',
        'discount_percent',
        'discount_amount',
        'min_order_value',
        'status',
    ];


    // Nếu không dùng created_at, updated_at thì bỏ timestamps
    public $timestamps = true;
}
