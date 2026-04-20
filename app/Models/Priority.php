<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

use App\Traits\UserScoped;

class Priority extends Model
{
    use UserScoped;

    protected $fillable = ['user_id', 'name', 'color_class', 'level', 'is_active'];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function workLogs()
    {
        return $this->hasMany(WorkLog::class);
    }
}
