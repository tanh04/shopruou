<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Visitor extends Model
{
    protected $fillable = ['session_id','ip','user_agent','visit_date','visited_at'];

    protected $casts = [
        'visit_date' => 'date',
        'visited_at' => 'datetime',
    ];

    public $timestamps = true; // <- Eloquent sẽ tự set created_at, updated_at
}

