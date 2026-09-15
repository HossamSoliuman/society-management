<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Member-portal gate: the login must be linked to an active member of an
 * active, subscribed society. Mirrors EnsureSocietyAccess for staff logins.
 */
class EnsureMemberAccess
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();
        $member = $user?->member()->with('society')->first();

        abort_unless($member, 403, 'Your account is not linked to a member record.');
        abort_unless($member->status === 'active', 403, 'Your member record is not active. Contact the society office.');

        $society = $member->society;
        abort_unless($society && $society->status === 'active', 403, 'This society is inactive.');
        abort_unless(
            in_array($society->subscription_status, ['active', 'expiring_soon'], true),
            403,
            'This society subscription is not active.'
        );

        if ($society->subscription_end_date) {
            $accessEndsAt = $society->subscription_end_date->copy()->addDays($society->grace_period_days ?? 0)->endOfDay();
            abort_if($accessEndsAt->isPast(), 403, 'This society subscription has expired.');
        }

        $user->setRelation('member', $member);
        $user->setRelation('society', $society);

        return $next($request);
    }
}
