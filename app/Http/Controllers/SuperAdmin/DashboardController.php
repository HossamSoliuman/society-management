<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Models\Payment;
use App\Models\Society;
use App\Models\Subscription;
use App\Models\SupportTicket;
use App\Models\Unit;
use App\Models\User;
use App\Services\PlatformBillingService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DashboardController extends Controller
{
    /** Revenue chart windows offered by the "Revenue Overview" selector. */
    private const REVENUE_RANGES = ['12' => 'Last 12 Months', '6' => 'Last 6 Months', 'year' => 'This Year'];

    public function index(Request $request, PlatformBillingService $billing): View
    {
        $now = Carbon::now();
        $monthStart = $now->copy()->startOfMonth();

        // One partition for both the stat cards and the status chart:
        // expired subscription first, then inactive, everything else active.
        $totalSocieties = Society::count();
        $expiredSocieties = Society::where('subscription_status', 'expired')->count();
        $inactiveSocieties = Society::where('subscription_status', '!=', 'expired')->where('status', '!=', 'active')->count();
        $activeSocieties = $totalSocieties - $expiredSocieties - $inactiveSocieties;
        $societyStatus = [
            'active' => $activeSocieties,
            'expired' => $expiredSocieties,
            'inactive' => $inactiveSocieties,
        ];

        $newSocieties = Society::where('created_at', '>=', $monthStart)->count();
        $expiringSoon = Society::where('status', 'active')->where('subscription_status', 'expiring_soon')->count();
        $lapsedThisMonth = Society::where('subscription_status', 'expired')
            ->whereBetween('subscription_end_date', [$monthStart, $now])
            ->count();

        // Same fallback as Society::getTotalUnitsAttribute(): declared counts until units are registered.
        $totalUnits = (int) Society::query()
            ->selectRaw('SUM(COALESCE(total_units, COALESCE(flats_count, 0) + COALESCE(shops_count, 0) + COALESCE(offices_count, 0))) as total')
            ->value('total');
        $newUnits = Unit::where('created_at', '>=', $monthStart)->count();

        $successful = fn () => Payment::query()->where('status', 'success');
        $currentMonthRevenue = (float) $successful()->whereBetween('payment_date', [$monthStart, $now->copy()->endOfMonth()])->sum('amount');
        $previousMonth = $now->copy()->subMonthNoOverflow();
        $previousMonthRevenue = (float) $successful()
            ->whereBetween('payment_date', [$previousMonth->copy()->startOfMonth(), $previousMonth->copy()->endOfMonth()])
            ->sum('amount');
        $revenueChange = $previousMonthRevenue > 0
            ? round(($currentMonthRevenue - $previousMonthRevenue) / $previousMonthRevenue * 100, 1)
            : null;
        $totalRevenue = (float) $successful()->sum('amount');

        $revenueRange = array_key_exists((string) $request->query('revenue'), self::REVENUE_RANGES)
            ? (string) $request->query('revenue')
            : '12';
        $monthlyRevenue = $this->monthlyRevenue($revenueRange, $now);

        $dailyRevenue = [];
        for ($i = 13; $i >= 0; $i--) {
            $day = $now->copy()->subDays($i);
            $dailyRevenue[] = [
                'label' => $day->format('d M'),
                'amount' => (float) $successful()->whereDate('payment_date', $day->toDateString())->sum('amount'),
            ];
        }

        $subscriptionStatus = [
            'active' => Subscription::where('status', 'active')->count(),
            'expiring_soon' => Subscription::where('status', 'expiring_soon')->count(),
            'expired' => Subscription::where('status', 'expired')->count(),
        ];

        return view('superadmin.dashboard.index', [
            'totalSocieties' => $totalSocieties,
            'activeSocieties' => $activeSocieties,
            'expiredSocieties' => $expiredSocieties,
            'newSocieties' => $newSocieties,
            'expiringSoon' => $expiringSoon,
            'lapsedThisMonth' => $lapsedThisMonth,
            'totalUnits' => $totalUnits,
            'newUnits' => $newUnits,
            'currentMonthRevenue' => $currentMonthRevenue,
            'revenueChange' => $revenueChange,
            'totalRevenue' => $totalRevenue,
            'monthlyRevenue' => $monthlyRevenue,
            'revenueRange' => $revenueRange,
            'revenueRanges' => self::REVENUE_RANGES,
            'dailyRevenue' => $dailyRevenue,
            'societyStatus' => $societyStatus,
            'subscriptionStatus' => $subscriptionStatus,
            'revenueByPlan' => $billing->revenueByPlan(),
            'recentSocieties' => Society::with('subscriptionPlan')->latest()->take(5)->get(),
            'recentPayments' => Payment::with('society')->latest()->take(5)->get(),
            'totalUsers' => User::where('status', 'active')->count(),
            'openTickets' => SupportTicket::where('status', 'open')->count(),
        ]);
    }

    /**
     * Successful platform payments per month for the selected window.
     *
     * @return array<int, array{month: string, amount: float}>
     */
    private function monthlyRevenue(string $range, Carbon $now): array
    {
        $months = match ($range) {
            '6' => collect(range(5, 0))->map(fn (int $i) => $now->copy()->startOfMonth()->subMonthsNoOverflow($i)),
            'year' => collect(range(1, 12))->map(fn (int $m) => Carbon::create($now->year, $m, 1)),
            default => collect(range(11, 0))->map(fn (int $i) => $now->copy()->startOfMonth()->subMonthsNoOverflow($i)),
        };

        return $months->map(fn (Carbon $month) => [
            'month' => $month->format('M'),
            'amount' => (float) Payment::query()
                ->where('status', 'success')
                ->whereBetween('payment_date', [$month->copy()->startOfMonth(), $month->copy()->endOfMonth()])
                ->sum('amount'),
        ])->all();
    }
}
