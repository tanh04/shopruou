<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Payment extends Model
{
    protected $table = 'payments';
    protected $primaryKey = 'payment_id';
    public $incrementing = true;
    protected $keyType = 'int';

    // Phương thức
    public const METHOD_MOMO  = 'MOMO';
    public const METHOD_VNPAY = 'VNPAY';
    public const METHOD_COD   = 'COD';

    // Trạng thái thanh toán
    public const STATUS_UNPAID  = 'Chưa thanh toán';
    public const STATUS_PENDING = 'Đang chờ xử lý';
    public const STATUS_PAID    = 'Đã thanh toán';
    public const STATUS_FAILED  = 'Thanh toán thất bại';

    protected $fillable = [
        'payment_method',
        'payment_status',
    ];

    protected $casts = [
        'payment_status' => 'string',
    ];

    public function isPaid(): bool
    {
        return $this->payment_status === self::STATUS_PAID;
    }
}
