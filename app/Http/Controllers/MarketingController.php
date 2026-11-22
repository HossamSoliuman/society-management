<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreDemoRequestRequest;
use App\Models\DemoRequest;
use App\Models\SubscriptionPlan;
use App\Models\User;
use App\Notifications\DemoRequestReceived;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Notification;
use Illuminate\View\View;

/**
 * Public marketing site: home, features, pricing and the demo request form.
 */
class MarketingController extends Controller
{
    public function home(): View
    {
        return view('marketing.home', ['plans' => $this->plans()]);
    }

    public function features(): View
    {
        return view('marketing.features');
    }

    public function pricing(): View
    {
        $plans = $this->plans();

        $moduleKeys = array_keys(SubscriptionPlan::MODULES);
        $sharedModules = array_values(array_filter(
            $moduleKeys,
            fn (string $key): bool => $plans->every(fn (SubscriptionPlan $plan): bool => in_array($key, $plan->enabledModuleKeys(), true)),
        ));

        return view('marketing.pricing', [
            'plans' => $plans,
            'sharedModules' => $sharedModules,
            'differingModules' => array_values(array_diff($moduleKeys, $sharedModules)),
        ]);
    }

    public function demo(Request $request): View
    {
        $plan = $request->filled('plan')
            ? SubscriptionPlan::query()->where('status', 'active')->where('code', $request->string('plan'))->first()
            : null;

        return view('marketing.demo', ['roles' => DemoRequest::ROLES, 'plan' => $plan]);
    }

    public function storeDemo(StoreDemoRequestRequest $request): RedirectResponse
    {
        // Honeypot: real visitors never see or fill the "website" field.
        if ($request->filled('website')) {
            return redirect()->route('demo.create')->with('demo_requested', true);
        }

        $demoRequest = DemoRequest::create($request->validated() + [
            'status' => 'new',
            'ip_address' => $request->ip(),
        ]);

        $admins = User::query()
            ->whereHas('roles', fn ($query) => $query->where('name', 'super_admin'))
            ->where('status', 'active')
            ->get();

        if ($admins->isNotEmpty()) {
            Notification::send($admins, new DemoRequestReceived($demoRequest));
        }

        return redirect()->route('demo.create')->with('demo_requested', true);
    }

    /**
     * @return Collection<int, SubscriptionPlan>
     */
    private function plans(): Collection
    {
        return SubscriptionPlan::query()
            ->with('modules')
            ->where('status', 'active')
            ->orderBy('priority')
            ->orderBy('amount')
            ->get();
    }
}
