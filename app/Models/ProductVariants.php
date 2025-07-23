<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;

class ProductVariants extends Model
{
    use LogsActivity;
    protected $guarded = ['id'];

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function serials()
    {
        return $this->hasMany(ProductVariantSerial::class, 'product_variant_id');
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
                            ->useLogName('Product Variant')
                            ->setDescriptionForEvent(fn(string $eventName) => "Product Variant has been updated")
                            ->logOnly([
                                'name',
                                'price',
                                'product_type',
                                'is_default',
                            ]);

    }
}
