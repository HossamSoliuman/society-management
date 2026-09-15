<?php

namespace App\Http\Controllers\Member;

use App\Http\Controllers\Controller;
use App\Models\Member;

/**
 * Base for member-portal screens: every query is scoped to the member record
 * linked to the signed-in "member" login (resolved by EnsureMemberAccess).
 */
abstract class PortalController extends Controller
{
    private ?Member $resolvedMember = null;

    protected function currentMember(): Member
    {
        $user = auth()->user();

        // The controller instance is cached on the route, so the memo must be
        // re-validated against the current user rather than trusted blindly.
        if ($this->resolvedMember instanceof Member && $user && $this->resolvedMember->user_id === $user->id) {
            return $this->resolvedMember;
        }

        $member = $user?->relationLoaded('member') ? $user->member : $user?->member()->first();

        abort_unless($member, 403, 'Your account is not linked to a member record.');

        return $this->resolvedMember = $member;
    }

    /**
     * 404 for any society-scoped row that is not owned by the current member.
     */
    protected function ownedByMember(?int $memberId): void
    {
        abort_unless($memberId !== null && $memberId === $this->currentMember()->id, 404);
    }
}
