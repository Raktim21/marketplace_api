<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;

class TermsAndCondition extends Model
{
    use LogsActivity;

    protected $guarded = ['id'];

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
                            ->useLogName('Terms & Condition')
                            ->setDescriptionForEvent(fn(string $eventName) => "Terms & Condition has been {$eventName}")
                            ->logOnly([
                                'title',
                                'description',
                            ]);

    }
}
