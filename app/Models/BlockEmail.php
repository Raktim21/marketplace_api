<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;

class BlockEmail extends Model
{
    use LogsActivity;
    protected $guarded = ['id'];
    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
                            ->useLogName('Banner Settings')
                            ->setDescriptionForEvent(fn(string $eventName) => "Banner Settings has been {$eventName}")
                            ->logOnly(['email', 'reason']);
        // Chain fluent methods for configuration options
    }
}
