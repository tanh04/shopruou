<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * @property int                             $review_id
 * @property int                             $product_id
 * @property int                             $user_id
 * @property int                             $rating
 * @property string|null                     $comment
 * @property int                             $status        0=Ẩn, 1=Hiện
 * @property bool                            $verified_purchase  // ✔ đã mua
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 *
 * @property-read string                     $status_label
 * @property-read \App\Models\Product        $product
 * @property-read \App\Models\User           $user
 *
 * @method static \Illuminate\Database\Eloquent\Builder|Review approved()
 * @method static \Illuminate\Database\Eloquent\Builder|Review forProduct(int $productId)
 * @method static \Illuminate\Database\Eloquent\Builder|Review byUser(int $userId)
 */
class Review extends Model
{
    use HasFactory;

    protected $table = 'reviews';
    protected $primaryKey = 'review_id';
    public $incrementing = true;
    protected $keyType = 'int';
    public $timestamps = true;

    protected $fillable = [
        'product_id',
        'user_id',
        'rating',
        'comment',
        'status',
        'verified_purchase',   // <- thêm vào fillable
    ];

    protected $casts = [
        'rating'            => 'integer',
        'status'            => 'integer',
        'verified_purchase' => 'boolean',
        'created_at'        => 'datetime',
        'updated_at'        => 'datetime',
    ];

    /**
     * Trạng thái đánh giá
     * 1 = Hiện, 0 = Ẩn
     */
    public const STATUS_HIDDEN   = 0;
    public const STATUS_APPROVED = 1;

    /** Giá trị mặc định */
    protected $attributes = [
        'status' => self::STATUS_HIDDEN,
    ];

    /** Quan hệ với Product */
    public function product()
    {
        return $this->belongsTo(Product::class, 'product_id', 'product_id');
    }

    /** Quan hệ với User */
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }

    /** Danh sách trạng thái (label) */
    public static function getStatusOptions(): array
    {
        return [
            self::STATUS_HIDDEN   => 'Ẩn',
            self::STATUS_APPROVED => 'Hiện',
        ];
    }

    /** Accessor: nhãn trạng thái */
    public function getStatusLabelAttribute(): string
    {
        return self::getStatusOptions()[$this->status] ?? 'Không rõ';
    }

    /* ===========================
     |         Scopes
     |===========================*/
    public function scopeApproved($query)
    {
        return $query->where('status', self::STATUS_APPROVED);
    }

    public function scopeForProduct($query, int $productId)
    {
        return $query->where('product_id', $productId);
    }

    public function scopeByUser($query, int $userId)
    {
        return $query->where('user_id', $userId);
    }

    /* ===========================
     |      Helpers/Mutators
     |===========================*/
    public function markApproved(): bool
    {
        $this->status = self::STATUS_APPROVED;
        return $this->save();
    }

    public function markHidden(): bool
    {
        $this->status = self::STATUS_HIDDEN;
        return $this->save();
    }
}
