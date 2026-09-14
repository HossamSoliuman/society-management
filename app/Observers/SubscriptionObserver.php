<?php

namespace App\Observers;

use App\Models\Society;
use App\Models\Subscription;

/**
 * Keeps the denormalised subscription columns on `societies` in sync so the
 * `subscriptions` table is the single source of truth (access checks read
 * the society row).
 */
class SubscriptionObserver
{
    public function saved(Subscription $subscription): void
    {
        $this->syncSociety($subscription->society_id);
    }

    public function deleted(Subscription $subscription): void
    {
        $this->syncSociety($subscription->society_id);
    }

    public function restored(Subscription $subscription): void
    {
        $this->syncSociety($subscription->society_id);
    }

    /**
     * Copy the governing subscription (latest non-cancelled by end date, else
     * the latest of any status) onto the society.
     */
    public static function syncSociety(?int $societyId): void
    {
        if (! $societyId) {
            return;
        }

        $society = Society::query()->find($societyId);
        if (! $society) {
            return;
        }

        $governing = Subscription::query()
            ->where('society_id', $societyId)
            ->where('status', '!=', 'cancelled')
            ->orderByDesc('end_date')
            ->orderByDesc('id')
            ->first()
            ?? Subscription::query()->where('society_id', $societyId)->latest('id')->first();

        if (! $governing) {
            return;
        }

        $society->forceFill([
            'subscription_plan_id' => $governing->plan_id,
            'subscription_start_date' => $governing->start_date,
            'subscription_end_date' => $governing->end_date,
            'billing_cycle' => $governing->billing_cycle,
            'subscription_status' => $governing->status,
        ])->saveQuietly();
    }
}
