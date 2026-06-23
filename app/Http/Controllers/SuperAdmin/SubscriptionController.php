<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Subscription;
use App\Models\SubscriptionPlan;
use App\Models\Society;

class SubscriptionController extends Controller
{
    public function plans()
    {
        $plans = SubscriptionPlan::with('modules')->latest()->paginate(10);
        return view('superadmin.subscription.plans', compact('plans'));
    }

    public function createPlan()
    {
        return view('superadmin.subscription.create-plan');
    }

    public function storePlan(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'code' => 'required|string|unique:subscription_plans,code|max:50',
            'plan_type' => 'required|in:basic,standard,premium,enterprise',
            'description' => 'nullable|string',
            'amount' => 'required|numeric|min:0',
            'max_units' => 'required|integer|min:1',
            'billing_cycle' => 'required|in:monthly,quarterly,half_yearly,yearly',
            'plan_duration' => 'required|in:1_month,3_months,6_months,1_year,2_years',
            'trial_period_days' => 'integer|min:0',
            'badge' => 'nullable|string|max:50',
            'color' => 'nullable|string|max:7',
            'priority' => 'integer|min:0',
            'status' => 'required|in:active,inactive',
        ]);

        SubscriptionPlan::create($validated);
        return redirect()->route('superadmin.subscription.plans')->with('success', 'Plan created successfully');
    }

    public function subscriptions()
    {
        $subscriptions = Subscription::with(['society', 'plan'])->latest()->paginate(10);
        return view('superadmin.subscription.subscriptions', compact('subscriptions'));
    }

    public function createSubscription()
    {
        $societies = Society::where('status', 'active')->get();
        $plans = SubscriptionPlan::where('status', 'active')->get();
        return view('superadmin.subscription.create-subscription', compact('societies', 'plans'));
    }

    public function storeSubscription(Request $request)
    {
        $validated = $request->validate([
            'society_id' => 'required|exists:societies,id',
            'plan_id' => 'required|exists:subscription_plans,id',
            'building_name' => 'nullable|string|max:255',
            'monthly_cost_per_flat' => 'required|numeric|min:0',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after:start_date',
            'additional_free_days' => 'integer|min:0',
            'billing_cycle' => 'required|in:monthly,quarterly,half_yearly,yearly',
            'description' => 'nullable|string',
            'payment_method' => 'nullable|string',
            'payment_date' => 'nullable|date',
            'reference_number' => 'nullable|string',
            'notes' => 'nullable|string',
        ]);

        $validated['subscription_number'] = 'SUB-' . date('Y') . '-' . str_pad(Subscription::count() + 1, 4, '0', STR_PAD_LEFT);
        $validated['status'] = 'active';

        Subscription::create($validated);

        return redirect()->route('superadmin.subscription.subscriptions')->with('success', 'Subscription created successfully');
    }

    public function renewals()
    {
        $upcomingRenewals = Subscription::with(['society', 'plan'])
            ->where('end_date', '<=', now()->addDays(60))
            ->where('status', '!=', 'cancelled')
            ->orderBy('end_date')
            ->paginate(10);

        return view('superadmin.subscription.renewals', compact('upcomingRenewals'));
    }
}
