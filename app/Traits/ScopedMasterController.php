<?php

namespace App\Traits;

/**
 * Determines whether the current request is via the "my-kra" route prefix
 * (user self-service) or the "masters" admin prefix.
 */
trait ScopedMasterController
{
    protected function isUserScoped(): bool
    {
        return request()->routeIs('my-kra.*');
    }

    protected function scopedUserId(): ?int
    {
        return $this->isUserScoped() ? auth()->id() : null;
    }
}
