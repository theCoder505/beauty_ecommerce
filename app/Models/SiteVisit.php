<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SiteVisit extends Model
{
    protected $fillable = [
        'session_id',
        'ip_address',
        'url',
        'device',
        'platform',
        'browser',
        'country',
        'country_code',
        'city',
        'user_agent',
        'visited_date',
    ];

    protected function casts(): array
    {
        return [
            'visited_date' => 'date',
        ];
    }
}
