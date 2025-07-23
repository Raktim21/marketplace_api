<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class UserRating extends Model
{
    protected $guarded = ['id'];

    // public $timestamps = false;

    public function order()
    {
        return $this->belongsTo(Order::class);
    }

}
