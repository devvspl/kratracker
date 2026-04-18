<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Priority extends Model
{
    protected $fillable = [
        'name',
        'color_class',
        'level',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function workLogs()
    {
        return $this->hasMany(WorkLog::class);
    }
}
