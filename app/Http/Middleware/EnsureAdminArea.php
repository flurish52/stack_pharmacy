<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Only staff-type roles may enter /admin/*.
 * Individual screens are still protected by their own `can:` permission.
 */
class EnsureAdminArea
{
    public const ROLES = ['staff', 'admin', 'owner', 'super_admin'];

    public function handle(Request $request, Closure $next): Response
    {
        abort_unless($request->user()?->hasAnyRole(self::ROLES), 403);

        return $next($request);
    }
}
