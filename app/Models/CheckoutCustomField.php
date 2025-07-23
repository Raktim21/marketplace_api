<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;

class CheckoutCustomField extends Model
{
    use LogsActivity;
    protected $guarded = ['id'];

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
                            ->useLogName('Checkout Custom Field')
                            ->setDescriptionForEvent(fn(string $eventName) => "A checkout custom field has been {$eventName}")
                            ->logOnly(['name', 'type', 'default_value', 'is_required']);

    }
}
