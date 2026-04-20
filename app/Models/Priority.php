<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

use App\Traits\UserScoped;

class Priority extends Model
{
    use UserScoped;

    protected $fillable = ['user_id', 'name'];

    public function workLogs()
    {
        return $this->hasMany(WorkLog::class);
    }
}
