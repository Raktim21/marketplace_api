<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;

class ServiceSection extends Model
{
    use LogsActivity;
    protected $guarded = ['id'];

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
                            ->useLogName('Service Section')
                            ->setDescriptionForEvent(fn(string $eventName) => "Service Section has been {$eventName}")
                            ->logOnly([
                                'title',
                                'subtitle',
                                'image',
                            ]);

    }
}
