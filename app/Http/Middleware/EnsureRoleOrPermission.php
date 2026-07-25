<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureRoleOrPermission
{
    public function handle(Request $request, Closure $next, string ...$abilities): Response
    {
        $user = $request->user();

        abort_unless(
            $user && ($user->hasAnyRole($abilities) || $user->hasAnyPermission($abilities)),
            403,
            'You are not authorized to perform this action.'
        );

        return $next($request);
    }
}
