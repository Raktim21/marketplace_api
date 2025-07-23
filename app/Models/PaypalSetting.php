<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;

class PaypalSetting extends Model
{
    use LogsActivity;

    protected $guarded = ['id'];

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
                            ->useLogName('Paypal Setting')
                            ->setDescriptionForEvent(fn(string $eventName) => "Paypal Setting has been {$eventName}")
                            ->logOnly([
                                'client_id',
                                'secret_key',
                            ]);

    }
}
