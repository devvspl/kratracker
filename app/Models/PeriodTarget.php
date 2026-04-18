<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PeriodTarget extends Model
{
    protected $fillable = [
        'sub_kra_id',
        'target_value',
        'period_type',
        'period_year',
        'period_month_or_quarter',
    ];

    protected $casts = [
        'target_value' => 'decimal:2',
    ];

    public function subKra()
    {
        return $this->belongsTo(SubKra::class);
    }
}
