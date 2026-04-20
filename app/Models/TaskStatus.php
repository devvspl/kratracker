<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

use App\Traits\UserScoped;

class TaskStatus extends Model
{
    use UserScoped;

    protected $fillable = ['user_id', 'name', 'sort_order'];

    public function workLogs()
    {
        return $this->hasMany(WorkLog::class, 'status_id');
    }
}
