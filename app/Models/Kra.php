<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Kra extends Model
{
    protected $fillable = [
        'name',
        'total_weightage',
        'description',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'total_weightage' => 'decimal:2',
    ];

    public function subKras()
    {
        return $this->hasMany(SubKra::class);
    }
}
