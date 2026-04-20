<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

use App\Traits\UserScoped;

class Application extends Model
{
    use UserScoped;

    protected $fillable = ['user_id', 'name', 'tech_stack', 'description', 'is_active'];

    public function modules()
    {
        return $this->hasMany(ApplicationModule::class)->where('is_active', true)->orderBy('name');
    }

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function workLogs()
    {
        return $this->hasMany(WorkLog::class);
    }
}
