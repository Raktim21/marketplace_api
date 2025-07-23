<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;

class FaqTitleSetting extends Model
{
    use LogsActivity;

    protected $guarded = ['id'];

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
                            ->useLogName('Faq Title Setting')
                            ->setDescriptionForEvent(fn(string $eventName) => "Faq Title Setting has been {$eventName}")
                            ->logOnly(['title', 'subtitle']);

    }
}
