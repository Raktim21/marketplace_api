<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;


class GroupProduct extends Model
{
    use LogsActivity;
    
    protected $guarded = ['id'];

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function group()
    {
        return $this->belongsTo(Product::class);
    }
    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
                            ->useLogName('Group')
                            ->setDescriptionForEvent(fn(string $eventName) => "A group has been {$eventName}")
                            ->logOnly([
                                'title',
                                'slug',
                                'description',
                                'email_message',
                                'user_id',
                                'image',
                                'is_top_rated',
                                'meta_title',
                                'meta_description',
                            ]);

    }
    
}
