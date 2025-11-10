<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * @property int $cart_id
 * @property int|null $user_id
 * @property string|null $session_id
 * @property string $status
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\CartItems> $items
 * @property-read int|null $items_count
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Cart newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Cart newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Cart query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Cart whereCartId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Cart whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Cart whereSessionId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Cart whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Cart whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Cart whereUserId($value)
 * @mixin \Eloquent
 */
class Cart extends Model
{
    protected $table = 'carts'; // nếu tên bảng là 'carts'
    protected $primaryKey = 'cart_id'; // QUAN TRỌNG nếu PK là cart_id
    public $incrementing = true;
    protected $keyType = 'int';

    protected $fillable = ['user_id','session_id','status'];

    public function items()
    {
        // chỉ rõ foreignKey và localKey cho chắc
        return $this->hasMany(CartItems::class, 'cart_id', 'cart_id');
    }
}
