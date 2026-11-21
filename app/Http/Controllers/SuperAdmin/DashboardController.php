<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Models\Invoice;
use App\Models\Payment;
use App\Models\Society;
use App\Models\Subscription;
use App\Models\SupportTicket;
use App\Models\Unit;
use App\Models\User;
use App\Services\PlatformBillingService;
use App\Support\Period;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DashboardController extends Controller
{
    private const DEFAULT_PERIOD = 'this_year';

    public function __construct(private PlatformBillingService $billing) {}

    public function index(Request $request): View
    {
        $period = Period::fromKey($request->query('range'), self::DEFAULT_PERIOD);
        $d = $this->buildData($period);

        return view('superadmin.dashboard.index', [
            'd' => $d,
            'range' => $period->key,
            'rangeOptions' => Period::options(),
            'societyStatus' => [
                'active' => $d['stats']['active_societies'],
                'expired' => $d['stats']['expired_societies'],
                'inactive' => $d['stats']['inactive_societies'],
            ],
            'subscriptionStatus' => [
                'active' => Subscription::where('status', 'active')->count(),
                'expiring_soon' => Subscription::where('status', 'expiring_soon')->count(),
                'expired' => Subscription::where('status', 'expired')->count(),
            ],
            'recentSocieties' => Society::with('subscriptionPlan')->latest()->take(5)->get(),
            'recentPayments' => Payment::with('society')->latest()->take(5)->get(),
            'totalUsers' => User::where('status', 'active')->count(),
            'openTickets' => SupportTicket::where('status', 'open')->count(),
        ]);
    }

    /**
     * AJAX endpoint: recompute the period-dependent cards and return their
     * rendered HTML fragments.
     */
    public function data(Request $request): JsonResponse
    {
        $period = Period::fromKey($request->query('range'), self::DEFAULT_PERIOD);
        $d = $this->buildData($period);

        return response()->json([
            'range' => $period->key,
            'rangeLabel' => $period->label(),
            'fragments' => [
                'stats' => view('superadmin.dashboard.partials.stats', ['d' => $d])->render(),
                'revenue' => view('superadmin.dashboard.partials.revenue', ['d' => $d])->render(),
                'plans' => view('superadmin.dashboard.partials.plans', ['d' => $d])->render(),
            ],
        ]);
    }

    /**
     * Every figure that depends on the selected period.
     *
     * @return array<string, mixed>
     */
    private function buildData(Period $period): array
    {
        $previous = $period->previous();

        // One partition for both the stat cards and the status chart:
        // expired subscription first, then inactive, everything else active.
        $totalSocieties = Society::count();
        $expiredSocieties = Society::where('subscription_status', 'expired')->count();
        $inactiveSocieties = Society::where('subscription_status', '!=', 'expired')->where('status', '!=', 'active')->count();

        // Same fallback as Society::getTotalUnitsAttribute(): declared counts until units are registered.
        $totalUnits = (int) Society::query()
            ->selectRaw('SUM(COALESCE(total_units, COALESCE(flats_count, 0) + COALESCE(shops_count, 0) + COALESCE(offices_count, 0))) as total')
            ->value('total');

        $successful = fn (): Builder => Payment::query()->where('status', 'success');
        $revenue = (float) $period->within($successful(), 'payment_date')->sum('amount');
        $previousRevenue = (float) $previous->within($successful(), 'payment_date')->sum('amount');

        return [
            'period' => $period,
            'stats' => [
                'total_societies' => $totalSocieties,
                'new_societies' => $period->within(Society::query(), 'created_at')->count(),
                'active_societies' => $totalSocieties - $expiredSocieties - $inactiveSocieties,
                'expiring_soon' => Society::where('status', 'active')->where('subscription_status', 'expiring_soon')->count(),
                'expired_societies' => $expiredSocieties,
                'inactive_societies' => $inactiveSocieties,
                'lapsed' => $period->within(Society::where('subscription_status', 'expired'), 'subscription_end_date')->count(),
                'total_units' => $totalUnits,
                'new_units' => $period->within(Unit::query(), 'created_at')->count(),
                'revenue' => $revenue,
                'revenue_change' => $previousRevenue > 0 ? round(($revenue - $previousRevenue) / $previousRevenue * 100, 1) : null,
                'total_revenue' => (float) $successful()->sum('amount'),
            ],
            'chart' => [
                'labels' => array_values($period->buckets()),
                'collected' => $period->series($period->within($successful(), 'payment_date'), 'payment_date', 'amount'),
                'invoiced' => $period->series($period->within(Invoice::query(), 'invoice_date'), 'invoice_date', 'total_amount'),
            ],
            'revenueByPlan' => $this->billing->revenueByPlan($period),
        ];
    }
}
