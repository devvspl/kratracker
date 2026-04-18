<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SubKra extends Model
{
    protected $fillable = [
        'kra_id',
        'name',
        'weightage',
        'unit',
        'measure_type',
        'logic_id',
        'review_period',
        'description',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'weightage' => 'decimal:2',
    ];

    public function kra()
    {
        return $this->belongsTo(Kra::class);
    }

    public function logic()
    {
        return $this->belongsTo(Logic::class);
    }

    public function periodTargets()
    {
        return $this->hasMany(PeriodTarget::class);
    }

    public function workLogs()
    {
        return $this->hasMany(WorkLog::class);
    }
}
