<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    protected $guarded = ['id'];

    public function customFields()
    {
        return $this->hasMany(OrderCustomField::class);
    }

    public function product()
    {
        return $this->belongsTo(Product::class,'product_id');
    }


    public function variant()
    {
        return $this->belongsTo(ProductVariants::class, 'product_variant_id');
    }

    public function review()
    {
        return $this->hasOne(UserRating::class);
    }

}
