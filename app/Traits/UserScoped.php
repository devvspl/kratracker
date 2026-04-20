<?php

namespace App\Traits;

use Illuminate\Database\Eloquent\Builder;

trait UserScoped
{
    /**
     * Scope: return records belonging to the current user OR global (user_id = null).
     */
    public function scopeForCurrentUser(Builder $query): Builder
    {
        if (auth()->check()) {
            return $query->where(function ($q) {
                $q->where('user_id', auth()->id())
                  ->orWhereNull('user_id');
            });
        }
        return $query->whereNull('user_id');
    }

    /**
     * Scope: return ONLY the current user's own records (for My KRA management).
     */
    public function scopeOwnedByUser(Builder $query): Builder
    {
        return $query->where('user_id', auth()->id());
    }

    public function user()
    {
        return $this->belongsTo(\App\Models\User::class);
    }
}
