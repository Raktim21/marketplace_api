<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;

class GeneralSetting extends Model
{
    use LogsActivity;

    protected $guarded = ['id'];

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
                            ->useLogName('General Setting')
                            ->setDescriptionForEvent(fn(string $eventName) => "General Setting has been {$eventName}")
                            ->logOnly([
                                'name',
                                'logo',
                                'favicon',
                                'address',
                                'email',
                                'phone',
                                'service_section',
                                'product_section',
                                'is_sold_count',
                                'is_average_rating',
                                'faq_section',
                                'discord_link',
                                'telegram_link',
                                'tiktok_link',
                                'youtube_link',
                                'seller_text',
                                'hover_color',
                                'primary_color',
                            ]);

    }
}
