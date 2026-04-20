<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

use App\Traits\UserScoped;

class Kra extends Model
{
    use UserScoped;

    protected $fillable = ['user_id', 'name', 'total_weightage', 'description', 'is_active'];

    protected $casts = [
        'is_active' => 'boolean',
        'total_weightage' => 'decimal:2',
    ];

    public function subKras()
    {
        return $this->hasMany(SubKra::class);
    }
}
