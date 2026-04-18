<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TaskStatus extends Model
{
    protected $fillable = [
        'name',
        'color_class',
        'sort_order',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function workLogs()
    {
        return $this->hasMany(WorkLog::class, 'status_id');
    }
}
