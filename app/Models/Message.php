<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Message extends Model
{
    protected $fillable = [
        'conversation_id','direction','sender_id','sender_name','body','read_at'
    ];
    protected $casts = ['read_at'=>'datetime'];

    public function conversation(): BelongsTo {
        return $this->belongsTo(Conversation::class);
    }
}
