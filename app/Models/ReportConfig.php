<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ReportConfig extends Model
{
    protected $fillable = [
        'recipient_user_id',
        'employee_user_id',
        'report_type',
        'is_active',
        'last_sent_at',
    ];

    protected $casts = [
        'is_active'    => 'boolean',
        'last_sent_at' => 'datetime',
    ];

    public function recipient()
    {
        return $this->belongsTo(User::class, 'recipient_user_id');
    }

    public function employee()
    {
        return $this->belongsTo(User::class, 'employee_user_id');
    }
}
