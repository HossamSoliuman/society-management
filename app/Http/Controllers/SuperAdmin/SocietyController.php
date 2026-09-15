<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Models\Role;
use App\Models\Society;
use App\Models\SocietyType;
use App\Models\SubscriptionPlan;
use App\Notifications\SocietyAdminInvitation;
use App\Services\AccountingService;
use App\Services\SubscriptionService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;
use Throwable;

class SocietyController extends Controller
{
    public function __construct(
        private readonly SubscriptionService $subscriptions,
        private readonly AccountingService $accounting,
    ) {}

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
            'admin_name' => 'required|string|max:255',
            'admin_email' => 'required|email|max:255|unique:users,email',
            'admin_mobile' => 'required|string|max:20',
        ]);

        $adminData = [
            'name' => $validated['admin_name'],
            'email' => $validated['admin_email'],
            'mobile' => $validated['admin_mobile'],
        ];
        unset($validated['admin_name'], $validated['admin_email'], $validated['admin_mobile']);

        $validated['auto_renewal'] = $request->boolean('auto_renewal', true);
        $validated['trial_period_days'] = $request->input('trial_period_days', 0);
        $validated['subscription_status'] = 'active';
        $validated['status'] = 'active';

        [$society, $admin, $token] = DB::transaction(function () use ($validated, $adminData) {
            $society = Society::create($validated);
            $admin = $society->users()->create($adminData + [
                'password' => Str::random(64),
                'status' => 'active',
            ]);
            $admin->roles()->attach(Role::where('name', 'society_admin')->firstOrFail());

            $this->accounting->seedChartFor($society);

            $plan = SubscriptionPlan::findOrFail($validated['subscription_plan_id']);
            $this->subscriptions->createForSociety($society, $plan, [
                'start_date' => $validated['subscription_start_date'],
                'end_date' => $validated['subscription_end_date'],
                'billing_cycle' => $validated['billing_cycle'],
                'additional_free_days' => (int) ($validated['trial_period_days'] ?? 0),
                'notes' => 'Initial subscription created with the society.',
            ]);

            return [$society, $admin, Password::broker()->createToken($admin)];
        });

        try {
            $admin->notify(new SocietyAdminInvitation($token, $society->name));
            $message = 'Society created and a password setup invitation was sent to '.$admin->email.'.';
        } catch (Throwable $exception) {
            report($exception);
            $message = 'Society and administrator created, but the invitation email could not be sent. Resend it from User Management.';
        }

        return redirect()->route('superadmin.societies.show', $society)->with('success', $message);
    }

    public function show(Society $society)
    {
        $society->load('subscriptionPlan', 'societyType', 'users.roles', 'subscriptions.plan', 'invoices');

        return view('superadmin.society.show', compact('society'));
    }

    public function edit(Society $society)
    {
        $societyTypes = SocietyType::where('status', 'active')->get();
        $plans = SubscriptionPlan::where('status', 'active')->get();
        $society->load('subscriptions.plan');

        return view('superadmin.society.edit', compact('society', 'societyTypes', 'plans'));
    }

    public function update(Request $request, Society $society)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'registration_number' => 'nullable|string|max:255',
            'prefix' => 'required|string|max:10|unique:societies,prefix,'.$society->id,
            'society_type_id' => 'required|exists:society_types,id',
            'registration_date' => 'nullable|date',
            'pan_number' => 'nullable|string|max:20',
            'flats_count' => 'nullable|integer|min:0',
            'shops_count' => 'nullable|integer|min:0',
            'offices_count' => 'nullable|integer|min:0',
            'address_line_1' => 'nullable|string|max:255',
            'address_line_2' => 'nullable|string|max:255',
            'city' => 'nullable|string|max:100',
            'state' => 'nullable|string|max:100',
            'pincode' => 'nullable|string|max:10',
            'primary_email' => 'nullable|email|max:255',
            'secondary_email' => 'nullable|email|max:255',
            'primary_mobile' => 'nullable|string|max:20',
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
            'grace_period_days' => 'nullable|integer|min:0',
            'auto_renewal' => 'boolean',
            'notes' => 'nullable|string',
            'status' => 'required|in:active,inactive',
        ]);

        $validated['auto_renewal'] = $request->boolean('auto_renewal', (bool) $society->auto_renewal);
        $wasInactive = $society->status === 'inactive';

        DB::transaction(function () use ($society, $validated, $wasInactive) {
            $society->update($validated);

            if ($validated['status'] === 'inactive') {
                $society->users()->update(['status' => 'inactive']);
            } elseif ($wasInactive) {
                $society->users()->where('status', 'inactive')->update(['status' => 'active']);
            }
        });

        return redirect()->route('superadmin.societies.show', $society)->with('success', 'Society updated successfully');
    }

    public function destroy(Society $society)
    {
        DB::transaction(function () use ($society) {
            $society->users()->update(['status' => 'inactive']);
            $society->delete();
        });

        return redirect()->route('superadmin.societies.index')->with('success', 'Society deleted successfully');
    }
}
