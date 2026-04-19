<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EmailContact extends Model
{
    protected $fillable = [
        'name', 'email', 'role', 'notes',
        'notify_on_complete', 'notify_on_status_change',
        'notify_on_daily_report', 'notify_on_weekly_report', 'notify_on_monthly_report',
        'is_active', 'created_by',
    ];

    protected $casts = [
        'notify_on_complete'       => 'boolean',
        'notify_on_status_change'  => 'boolean',
        'notify_on_daily_report'   => 'boolean',
        'notify_on_weekly_report'  => 'boolean',
        'notify_on_monthly_report' => 'boolean',
        'is_active'                => 'boolean',
    ];

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
