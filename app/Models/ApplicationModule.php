<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

use App\Traits\UserScoped;

class ApplicationModule extends Model
{
    use UserScoped;

    protected $fillable = ['user_id', 'application_id', 'name', 'is_active'];

    protected $casts = ['is_active' => 'boolean'];

    public function application()
    {
        return $this->belongsTo(Application::class);
    }

    public function workLogs()
    {
        return $this->hasMany(WorkLog::class, 'module_id');
    }
}
