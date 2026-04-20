<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

use App\Traits\UserScoped;

class Logic extends Model
{
    use UserScoped;

    protected $fillable = ['user_id', 'name', 'description', 'scoring_type'];

    public function subKras()
    {
        return $this->hasMany(SubKra::class);
    }
}
