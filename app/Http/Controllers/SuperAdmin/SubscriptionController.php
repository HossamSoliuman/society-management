<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Models\Society;
use App\Models\Subscription;
use App\Models\SubscriptionPlan;
use App\Services\SubscriptionService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class SubscriptionController extends Controller
{
    public function __construct(private readonly SubscriptionService $subscriptions) {}

    public function plans(): View
    {
        $plans = SubscriptionPlan::with('modules')->latest()->paginate(10);

        return view('superadmin.subscription.plans', compact('plans'));
    }

    public function createPlan(): View
    {
        return view('superadmin.subscription.create-plan');
    }

    public function storePlan(Request $request): RedirectResponse
    {
        $validated = $this->validatePlan($request);
        $modules = $validated['modules'] ?? [];
        unset($validated['modules']);

        $plan = SubscriptionPlan::create($validated);
        $plan->syncModules($modules);

        return redirect()->route('superadmin.subscription.plans')->with('success', 'Plan created successfully');
    }

    public function showPlan(SubscriptionPlan $plan): View
    {
        $plan->load('modules')->loadCount([
            'subscriptions',
            'subscriptions as active_subscriptions_count' => fn ($q) => $q->whereIn('status', ['active', 'expiring_soon']),
        ]);

        $recentSubscriptions = $plan->subscriptions()->with('society')->latest('id')->limit(10)->get();

        return view('superadmin.subscription.show-plan', compact('plan', 'recentSubscriptions'));
    }

    public function editPlan(SubscriptionPlan $plan): View
    {
        $plan->load('modules');

        return view('superadmin.subscription.edit-plan', compact('plan'));
    }

    public function updatePlan(Request $request, SubscriptionPlan $plan): RedirectResponse
    {
        $validated = $this->validatePlan($request, $plan);
        $modules = $validated['modules'] ?? [];
        unset($validated['modules']);

        $plan->update($validated);
        $plan->syncModules($modules);

        return redirect()->route('superadmin.subscription.plans.show', $plan)->with('success', 'Plan updated successfully');
    }

    public function togglePlanStatus(SubscriptionPlan $plan): RedirectResponse
    {
        $plan->update(['status' => $plan->status === 'active' ? 'inactive' : 'active']);

        return back()->with('success', "Plan {$plan->name} is now {$plan->status}.");
    }

    /**
     * Plans referenced by any subscription are kept for history; deactivate those instead.
     */
    public function destroyPlan(SubscriptionPlan $plan): RedirectResponse
    {
        if ($plan->subscriptions()->exists()) {
            return back()->with('error', "Plan {$plan->name} has subscriptions and cannot be deleted. Mark it inactive instead.");
        }

        $plan->delete();

        return redirect()->route('superadmin.subscription.plans')->with('success', 'Plan deleted successfully');
    }

    /**
     * @return array<string, mixed>
     */
    private function validatePlan(Request $request, ?SubscriptionPlan $plan = null): array
    {
        return $request->validate([
            'name' => 'required|string|max:255',
            'code' => ['required', 'string', 'max:50', Rule::unique('subscription_plans', 'code')->ignore($plan)],
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
            'modules' => 'nullable|array',
            'modules.*' => ['string', Rule::in(array_keys(SubscriptionPlan::MODULES))],
        ]);
    }

    public function subscriptions(Request $request): View
    {
        $subscriptions = Subscription::with(['society', 'plan', 'renewedFrom'])
            ->when($request->filled('status'), fn ($q) => $q->where('status', $request->string('status')))
            ->when($request->filled('society'), fn ($q) => $q->where('society_id', $request->integer('society')))
            ->latest('id')
            ->paginate(10)
            ->withQueryString();

        return view('superadmin.subscription.subscriptions', [
            'subscriptions' => $subscriptions,
            'societies' => Society::orderBy('name')->get(['id', 'name']),
            'statusCounts' => Subscription::query()
                ->selectRaw('status, COUNT(*) as c')
                ->groupBy('status')
                ->pluck('c', 'status'),
        ]);
    }

    public function createSubscription(): View
    {
        $societies = Society::where('status', 'active')->get();
        $plans = SubscriptionPlan::where('status', 'active')->get();

        return view('superadmin.subscription.create-subscription', compact('societies', 'plans'));
    }

    public function storeSubscription(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'society_id' => 'required|exists:societies,id',
            'plan_id' => 'required|exists:subscription_plans,id',
            'building_name' => 'nullable|string|max:255',
            'monthly_cost_per_flat' => 'required|numeric|min:0',
            'amount' => 'nullable|numeric|min:0',
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

        $society = Society::findOrFail($validated['society_id']);
        $plan = SubscriptionPlan::findOrFail($validated['plan_id']);
        unset($validated['society_id'], $validated['plan_id']);

        if (! isset($validated['amount'])) {
            $validated['amount'] = $plan->amount;
        }

        $subscription = $this->subscriptions->createForSociety($society, $plan, $validated);

        return redirect()->route('superadmin.subscription.subscriptions')
            ->with('success', "Subscription {$subscription->subscription_number} created successfully");
    }

    public function renewForm(Subscription $subscription): View
    {
        $subscription->load(['society', 'plan']);
        $plans = SubscriptionPlan::where('status', 'active')->orderBy('priority')->get();

        $start = max($subscription->end_date->copy()->addDay(), Carbon::today());

        return view('superadmin.subscription.renew', [
            'subscription' => $subscription,
            'plans' => $plans,
            'defaultStart' => $start,
            'defaultEnd' => $this->subscriptions->endDateFor($subscription->plan, $start),
        ]);
    }

    /**
     * Renew on the same plan or upgrade to another; both create a new row linked
     * through renewed_from_id so the history stays intact.
     */
    public function renew(Request $request, Subscription $subscription): RedirectResponse
    {
        abort_if($subscription->status === 'cancelled', 422, 'A cancelled subscription cannot be renewed. Create a new one instead.');

        $validated = $request->validate([
            'plan_id' => 'required|exists:subscription_plans,id',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after:start_date',
            'amount' => 'nullable|numeric|min:0',
            'payment_method' => 'nullable|string|max:100',
            'payment_date' => 'nullable|date',
            'reference_number' => 'nullable|string|max:100',
            'notes' => 'nullable|string',
        ]);

        $plan = SubscriptionPlan::findOrFail($validated['plan_id']);
        unset($validated['plan_id']);
        if (! isset($validated['amount'])) {
            $validated['amount'] = $plan->amount;
        }

        $renewed = $this->subscriptions->renew($subscription, $plan, $validated);
        $verb = $plan->is($subscription->plan) ? 'renewed' : 'upgraded';

        return redirect()->route('superadmin.subscription.subscriptions')
            ->with('success', "Subscription {$verb}: {$renewed->subscription_number} runs {$renewed->start_date->format('d M Y')} – {$renewed->end_date->format('d M Y')}.");
    }

    public function cancel(Request $request, Subscription $subscription): RedirectResponse
    {
        $validated = $request->validate(['reason' => 'nullable|string|max:255']);

        $this->subscriptions->cancel($subscription, $validated['reason'] ?? null);

        return back()->with('success', "Subscription {$subscription->subscription_number} cancelled.");
    }

    public function renewals(): View
    {
        $totalRenewals = Subscription::where('end_date', '<=', now()->addDays(60))
            ->where('status', '!=', 'cancelled')->count();
        $dueIn7Days = Subscription::whereBetween('end_date', [now(), now()->addDays(7)])->count();
        $dueIn30Days = Subscription::whereBetween('end_date', [now()->addDays(8), now()->addDays(30)])->count();
        $dueIn60Days = Subscription::whereBetween('end_date', [now()->addDays(31), now()->addDays(60)])->count();
        $overdue = Subscription::where('end_date', '<', now())->where('status', '!=', 'cancelled')->count();

        $renewals = Subscription::with(['society', 'plan'])
            ->where('end_date', '<=', now()->addDays(60))
            ->where('status', '!=', 'cancelled')
            ->orderBy('end_date')
            ->paginate(10);

        return view('superadmin.subscription.renewals', compact(
            'totalRenewals', 'dueIn7Days', 'dueIn30Days', 'dueIn60Days', 'overdue', 'renewals'
        ));
    }
}
