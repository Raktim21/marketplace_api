<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Str;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;

class Product extends Model
{
    use HasFactory, LogsActivity;

    protected $guarded = ['id'];

    // public function serials(){
    //     return $this->hasMany(ProductSerial::class);
    // }

    public function variants()
    {
        return $this->hasMany(ProductVariants::class);
    }

    public function orders()
    {
        return $this->hasMany(Order::class);
    }



    public function defaultVariant()
    {
        return $this->variants()->where('is_default', 1)->first();
    }
    

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function category()
    {
        return $this->belongsTo(Category::class,'category_id');
    }

    public function paymentGateways()
    {
        return $this->hasMany(ProductPaymentGateway::class);
    }


    public function groupProducts()
    {
        return $this->belongsToMany(Product::class, 'group_products', 'group_id', 'product_id');
    }

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            $model->uuid = (string) Str::uuid();
        });
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
                            ->useLogName('Product')
                            ->setDescriptionForEvent(fn(string $eventName) => "A product has been {$eventName}")
                            ->logOnly([
                                'title',
                                'slug',
                                'description',
                                'email_message',
                                'user_id',
                                'image',
                                'category_id',
                                'is_top_rated',
                                'meta_title',
                                'meta_description',
                            ]);

    }


}
