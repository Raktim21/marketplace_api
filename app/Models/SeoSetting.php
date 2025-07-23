<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;

class SeoSetting extends Model
{
    use LogsActivity;

    protected $guarded = ['id'];
    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
                            ->useLogName('Seo Setting')
                            ->setDescriptionForEvent(fn(string $eventName) => "Seo Setting has been {$eventName}")
                            ->logOnly([
                                'meta_title',
                                'meta_image',
                                'meta_description',
                                'meta_keywords',
                            ]);

    }
}
