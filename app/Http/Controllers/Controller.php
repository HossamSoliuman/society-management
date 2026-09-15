<?php

namespace App\Http\Controllers;

use App\Models\Society;

abstract class Controller
{
    /**
     * Memoized society for the authenticated user during the request.
     */
    private ?Society $resolvedSociety = null;

    /**
     * Resolve the society that belongs to the currently authenticated user.
     *
     * This scopes every society-panel query to the signed-in admin's own
     * society instead of blindly returning the first record in the table.
     */
    protected function currentSociety(): Society
    {
        $user = auth()->user();

        abort_if($user === null || $user->society_id === null, 403, 'Your account is not linked to a society.');

        // The controller instance is cached on the route, so the memo must be
        // re-validated against the current user rather than trusted blindly.
        if ($this->resolvedSociety instanceof Society && $this->resolvedSociety->id === $user->society_id) {
            return $this->resolvedSociety;
        }

        return $this->resolvedSociety = $user->society()->firstOrFail();
    }
}
