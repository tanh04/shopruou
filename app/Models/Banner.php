<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use Carbon\Carbon;

class Banner extends Model
{
    protected $table = 'banners';
    protected $fillable = [
        'title','image_path','link_url','position','sort_order','status','starts_at','ends_at'
    ];

    protected $casts = [
        'starts_at' => 'datetime',
        'ends_at'   => 'datetime',
    ];

    // Scope: chỉ banner đang hiệu lực + đang bật
    public function scopeActive(Builder $q): Builder
    {
        $now = Carbon::now();
        return $q->where('status', 1)
                 ->where(function($qq) use ($now) {
                    $qq->whereNull('starts_at')->orWhere('starts_at', '<=', $now);
                 })
                 ->where(function($qq) use ($now) {
                    $qq->whereNull('ends_at')->orWhere('ends_at', '>=', $now);
                 });
    }

    public function scopePosition(Builder $q, string $pos): Builder
    {
        return $q->where('position', $pos);
    }

        public const POSITIONS = [
        'home_top'     => 'Trang chủ (trên)',
        'home_mid'     => 'Trang chủ (giữa)',
        'sidebar_right'=> 'Sidebar (phải)',
        // 'footer'       => 'Footer',
    ];

    public function getIsActiveAttribute()
    {
        $now = Carbon::now();

        // Nếu có ngày bắt đầu mà chưa tới -> chưa hiệu lực
        if ($this->starts_at && $this->starts_at->gt($now)) {
            return false;
        }

        // Nếu có ngày kết thúc mà đã quá hạn -> hết hiệu lực
        if ($this->ends_at && $this->ends_at->lt($now)) {
            return false;
        }

        // Nếu status = 0 thì luôn ẩn
        return (bool) $this->status;
    }
}
