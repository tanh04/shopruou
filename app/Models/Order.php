<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\OrderItem; 

class Order extends Model
{
    use HasFactory;

    protected $table = 'orders';
    protected $primaryKey = 'order_id';
    public $incrementing = true;
    protected $keyType = 'int';
    public $timestamps = true;

    protected $fillable = [
        'user_id',
        'coupon_id',
        'order_name',
        'order_address',
        'order_phone',
        'order_email',
        'total_price',
        'discount_amount',
        'status',
        'order_note',
        'payment_id',
        // (tuỳ chọn nếu có) 'momo_order_id','momo_request_id'
    ];

    // ✅ THÊM trạng thái chờ thanh toán
    public const STATUS_WAITING_PAYMENT = 'Chờ thanh toán';
    public const STATUS_PENDING         = 'Đang xử lý';
    public const STATUS_CONFIRMED       = 'Đã xác nhận';
    public const STATUS_SHIPPING        = 'Đang giao';
    public const STATUS_COMPLETED       = 'Hoàn thành';
    public const STATUS_CANCELLED       = 'Đã hủy';

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }

    public function items()
    {
        return $this->hasMany(OrderItems::class, 'order_id', 'order_id');
    }

    public function payment()
    {
        return $this->belongsTo(Payment::class, 'payment_id', 'payment_id');
    }

    public function coupon()
    {
        return $this->belongsTo(Coupon::class, 'coupon_id', 'coupon_id');
    }

    public static function getStatusOptions()
    {
        return [
            self::STATUS_WAITING_PAYMENT,
            self::STATUS_PENDING,
            self::STATUS_CONFIRMED,
            self::STATUS_SHIPPING,
            self::STATUS_COMPLETED,
            self::STATUS_CANCELLED,
        ];
    }
}
