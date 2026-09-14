<?php

namespace App\Services;

use App\Models\PrefixSetting;
use App\Models\Society;
use App\Models\Subscription;
use App\Models\SubscriptionPlan;
use App\Notifications\SubscriptionRenewalReminder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class SubscriptionService
{
    /**
     * Days-before-expiry at which a renewal reminder is emailed.
     *
     * @var array<int, int>
     */
    public const REMINDER_DAYS = [30, 7, 1];

    /**
     * Create the initial subscription for a society (also syncs the society row).
     *
     * @param  array<string, mixed>  $attributes  start_date, end_date, billing_cycle, plan_id, ...
     */
    public function createForSociety(Society $society, SubscriptionPlan $plan, array $attributes): Subscription
    {
        return DB::transaction(function () use ($society, $plan, $attributes): Subscription {
            $subscription = new Subscription(array_merge([
                'building_name' => $society->name,
                'monthly_cost_per_flat' => 0,
                'amount' => $plan->amount,
                'billing_cycle' => $plan->billing_cycle,
                'additional_free_days' => 0,
            ], $attributes, [
                'society_id' => $society->id,
                'plan_id' => $plan->id,
                'subscription_number' => PrefixSetting::generate('Subscription'),
            ]));

            $subscription->status = $subscription->computeStatus();
            $subscription->save();

            return $subscription;
        });
    }

    /**
     * Renew (same plan) or upgrade (different plan) by issuing a new row that
     * starts the day after the previous end date, or today if already lapsed.
     *
     * @param  array<string, mixed>  $attributes  optional end_date, amount, payment_*, notes
     */
    public function renew(Subscription $previous, ?SubscriptionPlan $plan = null, array $attributes = []): Subscription
    {
        $plan ??= $previous->plan;
        $society = $previous->society;

        $start = isset($attributes['start_date'])
            ? Carbon::parse($attributes['start_date'])
            : max($previous->end_date->copy()->addDay(), Carbon::today());

        $end = isset($attributes['end_date'])
            ? Carbon::parse($attributes['end_date'])
            : $this->endDateFor($plan, $start);

        return DB::transaction(function () use ($previous, $plan, $society, $start, $end, $attributes): Subscription {
            if (in_array($previous->status, ['active', 'expiring_soon'], true) && $start->lessThanOrEqualTo($previous->end_date)) {
                // Upgrading mid-term: close the old row at the switch-over date.
                $previous->forceFill(['end_date' => $start->copy()->subDay(), 'status' => 'expired'])->saveQuietly();
            }

            $subscription = new Subscription(array_merge([
                'building_name' => $previous->building_name ?: $society->name,
                'monthly_cost_per_flat' => $previous->monthly_cost_per_flat,
                'amount' => $plan->amount,
                'billing_cycle' => $plan->billing_cycle,
                'additional_free_days' => 0,
            ], $attributes, [
                'society_id' => $society->id,
                'plan_id' => $plan->id,
                'renewed_from_id' => $previous->id,
                'subscription_number' => PrefixSetting::generate('Subscription'),
                'start_date' => $start,
                'end_date' => $end,
            ]));

            $subscription->status = $subscription->computeStatus();
            $subscription->save();

            return $subscription;
        });
    }

    public function cancel(Subscription $subscription, ?string $reason = null): Subscription
    {
        $subscription->forceFill([
            'status' => 'cancelled',
            'cancelled_at' => now(),
            'cancel_reason' => $reason,
        ])->save();

        return $subscription;
    }

    /**
     * Recompute active / expiring_soon / expired for every live subscription
     * and cascade to the society row. Returns the number of rows changed.
     */
    public function refreshStatuses(?Carbon $asOf = null): int
    {
        $asOf ??= Carbon::today();
        $changed = 0;

        Subscription::query()
            ->with('society')
            ->where('status', '!=', 'cancelled')
            ->orderBy('id')
            ->chunkById(200, function ($subscriptions) use ($asOf, &$changed) {
                foreach ($subscriptions as $subscription) {
                    $status = $subscription->computeStatus($asOf);
                    if ($status !== $subscription->status) {
                        $subscription->status = $status;
                        $subscription->save();
                        $changed++;
                    }
                }
            });

        return $changed;
    }

    /**
     * Email every society admin whose subscription ends in exactly one of the
     * reminder windows. Returns the number of subscriptions notified.
     */
    public function sendRenewalAlerts(?Carbon $asOf = null): int
    {
        $asOf ??= Carbon::today();
        $sent = 0;

        foreach (self::REMINDER_DAYS as $days) {
            $subscriptions = Subscription::query()
                ->with(['society.users.roles', 'plan'])
                ->current()
                ->whereDate('end_date', $asOf->copy()->addDays($days)->toDateString())
                ->get();

            foreach ($subscriptions as $subscription) {
                $admins = $subscription->society?->users
                    ->filter(fn ($user) => $user->status === 'active' && $user->hasRole('society_admin'));

                if (! $admins || $admins->isEmpty()) {
                    continue;
                }

                foreach ($admins as $admin) {
                    $admin->notify(new SubscriptionRenewalReminder($subscription, $days));
                }
                $sent++;
            }
        }

        return $sent;
    }

    /**
     * End date for a plan duration starting at $start (inclusive day count).
     */
    public function endDateFor(SubscriptionPlan $plan, Carbon $start): Carbon
    {
        $end = match ($plan->plan_duration) {
            '1_month' => $start->copy()->addMonthNoOverflow(),
            '3_months' => $start->copy()->addMonthsNoOverflow(3),
            '6_months' => $start->copy()->addMonthsNoOverflow(6),
            '2_years' => $start->copy()->addYears(2),
            default => $start->copy()->addYear(),
        };

        return $end->subDay();
    }
}
