<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\SubscriptionPlan;
use App\Models\PlanModule;

class SubscriptionPlanSeeder extends Seeder
{
    public function run(): void
    {
        $plans = [
            [
                'name' => 'Basic Plan',
                'code' => 'BASIC',
                'plan_type' => 'basic',
                'description' => 'Essential features for small societies',
                'amount' => 4999.00,
                'max_units' => 100,
                'billing_cycle' => 'yearly',
                'plan_duration' => '1_year',
                'badge' => null,
                'color' => '#2563EB',
                'priority' => 1,
            ],
            [
                'name' => 'Standard Plan',
                'code' => 'STANDARD',
                'plan_type' => 'standard',
                'description' => 'Advanced features for growing societies',
                'amount' => 8999.00,
                'max_units' => 250,
                'billing_cycle' => 'yearly',
                'plan_duration' => '1_year',
                'badge' => 'Popular',
                'color' => '#10B981',
                'priority' => 2,
            ],
            [
                'name' => 'Premium Plan',
                'plan_type' => 'premium',
                'code' => 'PREMIUM',
                'description' => 'Full-featured solution for large societies',
                'amount' => 14999.00,
                'max_units' => 500,
                'billing_cycle' => 'yearly',
                'plan_duration' => '1_year',
                'badge' => 'Best Value',
                'color' => '#F59E0B',
                'priority' => 3,
            ],
            [
                'name' => 'Enterprise Plan',
                'code' => 'ENTERPRISE',
                'plan_type' => 'enterprise',
                'description' => 'Custom solution with dedicated support',
                'amount' => 0.00,
                'max_units' => 9999,
                'billing_cycle' => 'yearly',
                'plan_duration' => '1_year',
                'badge' => 'Custom',
                'color' => '#8B5CF6',
                'priority' => 4,
            ],
        ];

        foreach ($plans as $plan) {
            SubscriptionPlan::create($plan);
        }

        $modules = [
            'Dashboard', 'User Management', 'Visitor Management', 'Complaint Management',
            'Facility Management', 'Revenue & Billing', 'Reports', 'Notifications',
            'Activity Logs', 'Document Management',
        ];

        $plans = SubscriptionPlan::all();
        foreach ($plans as $plan) {
            foreach ($modules as $module) {
                PlanModule::create([
                    'plan_id' => $plan->id,
                    'module_name' => $module,
                    'module_key' => strtolower(str_replace(' ', '_', $module)),
                    'description' => 'Access to ' . $module,
                    'is_enabled' => true,
                ]);
            }
        }
    }
}
