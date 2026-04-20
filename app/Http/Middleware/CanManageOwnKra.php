<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class CanManageOwnKra
{
    public function handle(Request $request, Closure $next)
    {
        if (auth()->check() && (auth()->user()->can_manage_own_kra || auth()->user()->hasRole('Admin'))) {
            return $next($request);
        }

        abort(403, 'You do not have permission to manage your own KRA configuration.');
    }
}
