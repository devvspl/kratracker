<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ForwardReason extends Model
{
    protected $fillable = ['reason', 'is_active'];
    protected $casts    = ['is_active' => 'boolean'];
}
