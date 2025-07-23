<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProductPaymentGateway extends Model
{
    protected $guarded = ['id'];

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function paymentGateway()
    {
        return $this->belongsTo(PaymentGateways::class);
    }
}
