<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ShippingFee extends Model
{
     protected $fillable = ['province', 'district', 'ward', 'fee'];
}
