<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * @property int $brand_id
 * @property string $brand_name
 * @property string $brand_description
 * @property int $brand_status
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Product> $products
 * @property-read int|null $products_count
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Brand newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Brand newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Brand query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Brand whereBrandDescription($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Brand whereBrandId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Brand whereBrandName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Brand whereBrandStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Brand whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Brand whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class Brand extends Model
{
        protected $table = 'brands';
        protected $primaryKey = 'brand_id';
        public $timestamps = true;

        protected $fillable = [
            'brand_name',
            'brand_description',
            'brand_status',
        ];

public function products(){ 
    return $this->hasMany(\App\Models\Product::class, 'brand_id', 'brand_id'); 
}
}
