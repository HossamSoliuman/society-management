<?php

namespace Database\Seeders;

use App\Models\PrefixSetting;
use App\Models\Society;
use App\Models\Subscription;
use Illuminate\Database\Seeder;

/**
 * One subscription row per society, derived from the society's plan/dates so the
 * `subscriptions` table is the source of truth (the observer syncs it back).
 */
class SubscriptionSeeder extends Seeder
{
    public function run(): void
    {
        Society::query()
            ->with('subscriptionPlan')
            ->whereNotNull('subscription_plan_id')
            ->doesntHave('subscriptions')
            ->each(function (Society $society) {
                $subscription = new Subscription([
                    'subscription_number' => PrefixSetting::generate('Subscription'),
                    'society_id' => $society->id,
                    'plan_id' => $society->subscription_plan_id,
                    'building_name' => $society->name,
                    'monthly_cost_per_flat' => 0,
                    'amount' => $society->subscriptionPlan?->amount ?? 0,
                    'start_date' => $society->subscription_start_date ?? now()->startOfYear(),
                    'end_date' => $society->subscription_end_date ?? now()->endOfYear(),
                    'billing_cycle' => $society->billing_cycle ?? 'yearly',
                    'payment_method' => 'Bank Transfer',
                    'payment_date' => $society->subscription_start_date ?? now()->startOfYear(),
                    'notes' => 'Seeded from society subscription columns.',
                ]);
                $subscription->status = $subscription->computeStatus();
                $subscription->save();
            });
    }
}
