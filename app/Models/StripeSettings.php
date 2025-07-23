<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;

class StripeSettings extends Model
{
    use LogsActivity;

    protected $guarded = ['id'];

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
                            ->useLogName('Stripe Settings')
                            ->setDescriptionForEvent(fn(string $eventName) => "Stripe Setting has been {$eventName}")
                            ->logOnly([
                                'secret_key',
                                'public_key',
                            ]);

    }
}
