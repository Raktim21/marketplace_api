<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;

class FAQ extends Model
{
    use LogsActivity;
    protected $guarded = ['id'];

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
                            ->useLogName('FAQ')
                            ->setDescriptionForEvent(fn(string $eventName) => "A faq has been {$eventName}")
                            ->logOnly(['question', 'answer']);

    }
}
