<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Cart extends Model
{
    protected $guarded = ['id'];

    public function variant()
    {
        return $this->belongsTo(ProductVariants::class, 'product_variant_id');
    }
}
