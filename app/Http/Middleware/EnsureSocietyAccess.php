<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureSocietyAccess
{
    public function handle(Request $request, Closure $next): Response
    {
        $society = $request->user()?->society;

        abort_unless($society, 403, 'Your account is not linked to a society.');
        abort_unless($society->status === 'active', 403, 'This society is inactive.');
        abort_unless(
            in_array($society->subscription_status, ['active', 'expiring_soon'], true),
            403,
            'This society subscription is not active.'
        );

        if ($society->subscription_end_date) {
            $accessEndsAt = $society->subscription_end_date->copy()->addDays($society->grace_period_days ?? 0)->endOfDay();
            abort_if($accessEndsAt->isPast(), 403, 'This society subscription has expired.');
        }

        return $next($request);
    }
}
