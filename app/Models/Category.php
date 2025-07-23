<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;

class Category extends Model
{
    use HasFactory, LogsActivity;

    protected $guarded = ['id'];


    public function products()
    {
        return $this->hasMany(Product::class);
    }
    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
                            ->useLogName('Category')
                            ->setDescriptionForEvent(fn(string $eventName) => "A category has been {$eventName}")
                            ->logOnly(['name', 'image']);
        // Chain fluent methods for configuration options
    }
    
}
