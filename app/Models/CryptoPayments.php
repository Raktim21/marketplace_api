<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CryptoPayments extends Model
{
    protected $guarded = ['id'];

    public function order()
    {
        return  $this->belongsTo(Order::class);
    }
}
