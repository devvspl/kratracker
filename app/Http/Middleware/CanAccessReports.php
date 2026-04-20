<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class CanAccessReports
{
    public function handle(Request $request, Closure $next)
    {
        $user = auth()->user();

        if ($user && ($user->hasRole(['Admin', 'Manager']) || $user->can_manage_own_kra)) {
            return $next($request);
        }

        abort(403, 'Access denied.');
    }
}
