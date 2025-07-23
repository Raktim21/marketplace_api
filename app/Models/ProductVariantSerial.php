<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProductVariantSerial extends Model
{
    protected $guarded = ['id'];

    public function productVariant()
    {
        return $this->belongsTo(ProductVariants::class);
    }
}
