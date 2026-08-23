<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureUserHasPermission
{
    public function handle(Request $request, Closure $next, string $module, string $action = 'View'): Response
    {
        if (! $request->user()?->hasPermission($module, $action)) {
            abort(403, 'You do not have permission to access this area.');
        }

        return $next($request);
    }
}
