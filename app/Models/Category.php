<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * @property int $category_id
 * @property string $category_name
 * @property string $category_description
 * @property int $category_status
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Product> $products
 * @property-read int|null $products_count
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Category newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Category newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Category query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Category whereCategoryDescription($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Category whereCategoryId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Category whereCategoryName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Category whereCategoryStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Category whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Category whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class Category extends Model
{
    protected $table = 'categories';
    protected $primaryKey = 'category_id';
    public $timestamps = true;

    protected $fillable = [
        'category_name',
        'category_description',
        'category_status',
        'parent_id',
    ];

    public function children()
    {
        return $this->hasMany(Category::class, 'parent_id', 'category_id')
                    ->where('category_status', 1)
                    ->with('children'); // đệ quy
    }

    public function parent()
    {
        return $this->belongsTo(Category::class, 'parent_id', 'category_id');
    }

    public function products(){ 
        return $this->hasMany(Product::class, 'category_id', 'category_id');
    }

}
