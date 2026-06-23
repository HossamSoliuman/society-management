<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Society;
use App\Models\SocietyType;
use App\Models\SubscriptionPlan;

class SocietyController extends Controller
{
    public function index()
    {
        $societies = Society::with('subscriptionPlan')->latest()->paginate(10);
        return view('superadmin.society.index', compact('societies'));
    }

    public function create()
    {
        $societyTypes = SocietyType::where('status', 'active')->get();
        $plans = SubscriptionPlan::where('status', 'active')->get();
        return view('superadmin.society.create', compact('societyTypes', 'plans'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'registration_number' => 'nullable|string|max:255',
            'prefix' => 'required|string|unique:societies,prefix|max:10',
            'society_type_id' => 'required|exists:society_types,id',
            'registration_date' => 'nullable|date',
            'pan_number' => 'nullable|string|max:20',
            'flats_count' => 'required|integer|min:0',
            'shops_count' => 'required|integer|min:0',
            'offices_count' => 'required|integer|min:0',
            'address_line_1' => 'required|string|max:255',
            'address_line_2' => 'nullable|string|max:255',
            'city' => 'required|string|max:100',
            'state' => 'required|string|max:100',
            'pincode' => 'required|string|max:10',
            'primary_email' => 'required|email|max:255',
            'secondary_email' => 'nullable|email|max:255',
            'primary_mobile' => 'required|string|max:20',
            'alternate_mobile' => 'nullable|string|max:20',
            'landline' => 'nullable|string|max:20',
            'website' => 'nullable|url|max:255',
            'chairman_name' => 'nullable|string|max:255',
            'chairman_mobile' => 'nullable|string|max:20',
            'chairman_email' => 'nullable|email|max:255',
            'secretary_name' => 'nullable|string|max:255',
            'secretary_mobile' => 'nullable|string|max:20',
            'secretary_email' => 'nullable|email|max:255',
            'treasurer_name' => 'nullable|string|max:255',
            'treasurer_mobile' => 'nullable|string|max:20',
            'treasurer_email' => 'nullable|email|max:255',
            'subscription_plan_id' => 'required|exists:subscription_plans,id',
            'subscription_start_date' => 'required|date',
            'subscription_end_date' => 'required|date|after:subscription_start_date',
            'billing_cycle' => 'required|in:monthly,quarterly,half_yearly,yearly',
            'grace_period_days' => 'required|integer|min:0',
            'auto_renewal' => 'boolean',
            'trial_period_days' => 'integer|min:0',
            'notes' => 'nullable|string',
        ]);

        $validated['auto_renewal'] = $request->boolean('auto_renewal', true);
        $validated['trial_period_days'] = $request->input('trial_period_days', 0);
        $validated['subscription_status'] = 'active';
        $validated['status'] = 'active';

        Society::create($validated);

        return redirect()->route('superadmin.societies.index')->with('success', 'Society created successfully');
    }

    public function show(Society $society)
    {
        $society->load('subscriptionPlan', 'societyType');
        return view('superadmin.society.show', compact('society'));
    }

    public function edit(Society $society)
    {
        $societyTypes = SocietyType::where('status', 'active')->get();
        $plans = SubscriptionPlan::where('status', 'active')->get();
        return view('superadmin.society.edit', compact('society', 'societyTypes', 'plans'));
    }

    public function update(Request $request, Society $society)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'registration_number' => 'nullable|string|max:255',
            'prefix' => 'required|string|max:10|unique:societies,prefix,' . $society->id,
            'society_type_id' => 'required|exists:society_types,id',
            'status' => 'required|in:active,inactive',
        ]);

        $society->update($validated);
        return redirect()->route('superadmin.societies.index')->with('success', 'Society updated successfully');
    }

    public function destroy(Society $society)
    {
        $society->delete();
        return redirect()->route('superadmin.societies.index')->with('success', 'Society deleted successfully');
    }
}
