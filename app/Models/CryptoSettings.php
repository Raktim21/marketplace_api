<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;

class CryptoSettings extends Model
{
    use LogsActivity;

    protected $guarded = ['id'];

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
                            ->useLogName('Sellhub Payment Api Setting')
                            ->setDescriptionForEvent(fn(string $eventName) => "Sellhub Payment Api Setting has been {$eventName}")
                            ->logOnly([
                                'api_key',
                                'btc_status',
                                'ltc_status',
                                'solana_status',
                                'eth_status',
                            ]);

    }
}
