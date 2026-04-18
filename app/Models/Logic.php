<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Logic extends Model
{
    protected $fillable = [
        'name',
        'description',
        'scoring_type',
    ];

    public function subKras()
    {
        return $this->hasMany(SubKra::class);
    }
}
