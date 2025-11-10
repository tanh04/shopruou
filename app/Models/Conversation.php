<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Conversation extends Model
{
    protected $fillable = [
        'session_id','user_id','customer_name','customer_contact','status','last_message_at'
    ];
    protected $casts = ['last_message_at' => 'datetime'];

    public function messages(): HasMany {
        return $this->hasMany(Message::class);
    }
}
