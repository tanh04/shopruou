<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Statistical extends Model
{
    protected $table = 'statistical';

    protected $fillable = [
        'order_date', 'order_count', 'sales', 'profit', 'quantity'
    ];

    protected $casts = [
        'order_date' => 'date',
        'order_count' => 'integer',
        'sales' => 'integer',
        'profit' => 'integer',
        'quantity' => 'integer',
    ];

    /** Upsert theo ngày (tạo mới hoặc cộng dồn) */
    public static function upsertDaily(string|\DateTimeInterface $date, array $values): self
    {
        $d = $date instanceof \DateTimeInterface ? $date->format('Y-m-d') : $date;

        $row = static::firstOrNew(['order_date' => $d]);

        $row->order_count = ($row->order_count ?? 0) + ($values['order_count'] ?? 0);
        $row->sales       = ($row->sales ?? 0)       + ($values['sales'] ?? 0);
        $row->profit      = ($row->profit ?? 0)      + ($values['profit'] ?? 0);
        $row->quantity    = ($row->quantity ?? 0)    + ($values['quantity'] ?? 0);

        $row->save();
        return $row;
    }

    /** Lấy data trong khoảng ngày (bao gồm 2 đầu mút) */
    public function scopeBetweenDates($q, $from, $to)
    {
        return $q->whereBetween('order_date', [$from, $to])->orderBy('order_date');
    }
}
