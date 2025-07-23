<?php

namespace App\Models;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;

use Illuminate\Database\Eloquent\Model;

class BannerSetting extends Model
{    
    use LogsActivity;
    protected $guarded = ['id'];

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
                            ->useLogName('Banner Settings')
                            ->setDescriptionForEvent(fn(string $eventName) => "Banner Settings has been {$eventName}")
                            ->logOnly(['title', 'subtitle', 'image', 'is_image']);
        // Chain fluent methods for configuration options
    }
}
