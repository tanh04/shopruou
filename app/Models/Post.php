<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;
class Post extends Model
{
     use SoftDeletes;

    protected $fillable = [
        'category_id','title','slug','excerpt','content','published','published_at'
    ];

    protected $casts = [
        'published' => 'bool',
        'published_at' => 'datetime',
    ];

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            if (empty($model->slug)) {
                $model->slug = static::uniqueSlug($model->title);
            }
            if ($model->published && empty($model->published_at)) {
                $model->published_at = now();
            }
        });

        static::updating(function ($model) {
            if ($model->isDirty('title') && empty($model->slug)) {
                $model->slug = static::uniqueSlug($model->title, $model->id);
            }
            if ($model->isDirty('published') && $model->published && empty($model->published_at)) {
                $model->published_at = now();
            }
        });
    }

    protected static function uniqueSlug(string $title, ?int $ignoreId = null): string
    {
        $base = Str::slug($title);
        $slug = $base;
        $i = 1;
        while (static::where('slug', $slug)
            ->when($ignoreId, fn($q)=>$q->where('id','!=',$ignoreId))
            ->exists()) {
            $slug = $base.'-'.$i++;
        }
        return $slug;
    }
}
