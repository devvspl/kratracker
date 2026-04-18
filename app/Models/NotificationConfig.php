<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class NotificationConfig extends Model
{
    protected $fillable = [
        'event_type',
        'is_email_enabled',
        'email_template',
        'is_active',
    ];

    protected $casts = [
        'is_email_enabled' => 'boolean',
        'is_active' => 'boolean',
    ];
}
