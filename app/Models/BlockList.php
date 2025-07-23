<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;

class BlockList extends Model
{
    use LogsActivity;

    protected $guarded = ['id'];

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
                            ->useLogName('Block List')
                            ->setDescriptionForEvent(fn(string $eventName) => "A block list has been {$eventName}")
                            ->logOnly(['value', 'type', 'reason']);
        // Chain fluent methods for configuration options
    }
}
