<?php

namespace App\Http\Controllers\Society;

use App\Http\Controllers\Controller;
use App\Models\CollectionPayment;
use App\Models\MaintenanceBill;
use App\Models\Member;
use App\Models\Notice;
use App\Models\Society;
use App\Models\SupportTicket;
use App\Models\Unit;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DashboardController extends Controller
{
    /**
     * Selectable reporting periods for the dashboard.
     *
     * @return array<string, string>
     */
    private function rangeOptions(): array
    {
        return [
            'this_month' => 'This Month',
            'last_month' => 'Last Month',
            'this_year' => 'This Year',
            'last_year' => 'Last Year',
            'all' => 'All Time',
        ];
    }

    public function index(): View
    {
        $society = Society::firstOrFail();
        $range = 'this_year';

        return view('society.dashboard.index', [
            'd' => $this->buildData($society, $range),
            'range' => $range,
            'rangeOptions' => $this->rangeOptions(),
        ]);
    }

    /**
     * AJAX endpoint: recompute the dashboard for a period and return the
     * rendered HTML fragments for the dynamic regions.
     */
    public function data(Request $request): JsonResponse
    {
        $society = Society::firstOrFail();

        $range = (string) $request->query('range', 'this_year');
        if (! array_key_exists($range, $this->rangeOptions())) {
            $range = 'this_year';
        }

        $d = $this->buildData($society, $range);

        return response()->json([
            'range' => $range,
            'rangeLabel' => $this->rangeOptions()[$range],
            'fragments' => [
                'stats' => view('society.partials.dashboard.stats', ['d' => $d])->render(),
                'collection' => view('society.partials.dashboard.collection', ['d' => $d])->render(),
                'revenue' => view('society.partials.dashboard.revenue', ['d' => $d])->render(),
                'occupancy' => view('society.partials.dashboard.occupancy', ['d' => $d])->render(),
                'complaint' => view('society.partials.dashboard.complaint', ['d' => $d])->render(),
            ],
        ]);
    }

    /**
     * Resolve a period key to a date window plus the reference year used for
     * the 12-month revenue trend.
     *
     * @return array{start: ?Carbon, end: ?Carbon, refYear: int}
     */
    private function rangeWindow(string $range): array
    {
        $now = Carbon::now();

        return match ($range) {
            'this_month' => [
                'start' => $now->copy()->startOfMonth(),
                'end' => $now->copy()->endOfMonth(),
                'refYear' => (int) $now->year,
            ],
            'last_month' => [
                'start' => $now->copy()->subMonthNoOverflow()->startOfMonth(),
                'end' => $now->copy()->subMonthNoOverflow()->endOfMonth(),
                'refYear' => (int) $now->copy()->subMonthNoOverflow()->year,
            ],
            'last_year' => [
                'start' => $now->copy()->subYear()->startOfYear(),
                'end' => $now->copy()->subYear()->endOfYear(),
                'refYear' => (int) $now->year - 1,
            ],
            'all' => [
                'start' => null,
                'end' => null,
                'refYear' => (int) $now->year,
            ],
            default => [ // this_year
                'start' => $now->copy()->startOfYear(),
                'end' => $now->copy()->endOfYear(),
                'refYear' => (int) $now->year,
            ],
        };
    }

    /**
     * Compute every dashboard figure from live data for the given period.
     *
     * @return array<string, mixed>
     */
    private function buildData(Society $society, string $range): array
    {
        $sid = $society->id;
        $window = $this->rangeWindow($range);
        $start = $window['start'];
        $end = $window['end'];

        // --- Members (totals are a live snapshot; "new" is period-scoped) ---
        $totalMembers = Member::where('society_id', $sid)->where('status', 'active')->count();
        $newMembers = Member::where('society_id', $sid)
            ->when($start, fn ($q) => $q->whereBetween('join_date', [$start, $end]))
            ->count();

        // --- Units / occupancy (live snapshot) ---
        $unitsByStatus = Unit::where('society_id', $sid)
            ->selectRaw('status, COUNT(*) as c')
            ->groupBy('status')
            ->pluck('c', 'status');
        $occupied = (int) ($unitsByStatus['occupied'] ?? 0);
        $vacant = (int) ($unitsByStatus['vacant'] ?? 0);
        $maintenance = (int) ($unitsByStatus['under_maintenance'] ?? 0);
        $totalUnits = $occupied + $vacant + $maintenance;
        $occupancyPct = $totalUnits > 0 ? (int) round($occupied / $totalUnits * 100) : 0;

        // --- Maintenance bills (period-scoped) drive the money figures ---
        $billBase = fn () => MaintenanceBill::where('society_id', $sid)
            ->when($start, fn ($q) => $q->whereBetween('bill_date', [$start, $end]));

        $billAgg = $billBase()
            ->selectRaw('COALESCE(SUM(total_amount),0) demand, COALESCE(SUM(collected_amount),0) collected, COALESCE(SUM(outstanding_amount),0) outstanding')
            ->first();
        $demand = (float) $billAgg->demand;
        $collected = (float) $billAgg->collected;
        $outstanding = (float) $billAgg->outstanding;

        $overdue = (float) $billBase()->where('status', 'overdue')->sum('outstanding_amount');
        $pending = (float) $billBase()->whereIn('status', ['pending', 'partial'])->sum('outstanding_amount');
        $collectedPct = $demand > 0 ? (int) round($collected / $demand * 100) : 0;
        $pendingMembers = (int) $billBase()->where('outstanding_amount', '>', 0)->distinct()->count('member_id');

        // --- 12-month revenue trend for the reference year ---
        $monthly = MaintenanceBill::where('society_id', $sid)
            ->whereRaw("strftime('%Y', bill_date) = ?", [(string) $window['refYear']])
            ->selectRaw("CAST(strftime('%m', bill_date) AS INTEGER) as m, COALESCE(SUM(collected_amount),0) as v")
            ->groupBy('m')
            ->pluck('v', 'm');

        $points = [];
        $months = [];
        for ($m = 1; $m <= 12; $m++) {
            $points[] = (float) ($monthly[$m] ?? 0);
            $months[] = date('M', mktime(0, 0, 0, $m, 1));
        }
        $ytd = array_sum($points);

        // Compare against the same year prior.
        $prevYtd = (float) MaintenanceBill::where('society_id', $sid)
            ->whereRaw("strftime('%Y', bill_date) = ?", [(string) ($window['refYear'] - 1)])
            ->sum('collected_amount');
        $vsLastYear = $prevYtd > 0 ? round(($ytd - $prevYtd) / $prevYtd * 100, 1) : null;

        // --- Complaints (from support tickets) ---
        $ticketsByStatus = SupportTicket::where('society_id', $sid)
            ->selectRaw('status, COUNT(*) as c')
            ->groupBy('status')
            ->pluck('c', 'status');
        $cOpen = (int) ($ticketsByStatus['open'] ?? 0);
        $cProgress = (int) ($ticketsByStatus['in_progress'] ?? 0);
        $cResolved = (int) (($ticketsByStatus['resolved'] ?? 0) + ($ticketsByStatus['closed'] ?? 0));
        $highPriority = (int) SupportTicket::where('society_id', $sid)
            ->whereIn('status', ['open', 'in_progress'])
            ->whereIn('priority', ['high', 'urgent', 'critical'])
            ->count();

        // --- Recent activity (latest collections) & notices (not period-scoped) ---
        $activities = CollectionPayment::where('society_id', $sid)
            ->latest('receipt_date')
            ->limit(5)
            ->get()
            ->map(fn ($p) => [
                'icon' => 'fa-indian-rupee-sign',
                'variant' => 'is-success',
                'title' => 'Payment received from',
                'subtitle' => trim(($p->flat_number ?: $p->unit_label ?: $p->member_name) ?: 'Member'),
                'time' => optional($p->receipt_date)->format('d M, h:i A') ?? '',
                'amount' => $p->paid_amount ? $this->inr($p->paid_amount) : null,
            ])->all();

        $notices = Notice::orderByDesc('publish_at')
            ->limit(3)
            ->get()
            ->map(fn ($n) => [
                'icon' => 'fa-bullhorn',
                'variant' => match ($n->priority) {
                    'high', 'urgent' => 'is-danger',
                    'medium' => 'is-warning',
                    default => 'is-info',
                },
                'title' => $n->title,
                'desc' => $n->short_description ?: str($n->content ?? '')->limit(90),
                'date' => optional($n->publish_at)->format('d M Y') ?? '',
            ])->all();

        return [
            'range' => $range,
            'range_label' => $this->rangeOptions()[$range],
            'stats' => [
                'total_members' => $totalMembers,
                'new_members' => $newMembers,
                'total_units' => $totalUnits,
                'occupancy_pct' => $occupancyPct,
                'monthly_collections' => $collected,
                'monthly_collections_fmt' => $this->inr($collected),
                'collected_pct' => $collectedPct,
                'pending_dues' => $outstanding,
                'pending_dues_fmt' => $this->inr($outstanding),
                'pending_members' => $pendingMembers,
                'open_complaints' => $cOpen,
                'high_priority' => $highPriority,
            ],
            'collection' => [
                'collected' => $collected,
                'pending' => $pending,
                'overdue' => $overdue,
                'total_demand' => $demand,
                'collected_fmt' => $this->inr($collected),
                'pending_fmt' => $this->inr($pending),
                'overdue_fmt' => $this->inr($overdue),
                'total_demand_fmt' => $this->inr($demand),
                'collected_pct' => $collectedPct,
                'pending_pct' => $demand > 0 ? (int) round($pending / $demand * 100) : 0,
                'overdue_pct' => $demand > 0 ? (int) round($overdue / $demand * 100) : 0,
            ],
            'revenue' => [
                'points' => $points,
                'months' => $months,
                'max' => $this->niceCeil(max($points) ?: 1),
                'ytd' => $ytd,
                'ytd_fmt' => $this->inr($ytd),
                'ref_year' => $window['refYear'],
                'vs_last_year' => $vsLastYear,
            ],
            'occupancy' => [
                'occupied' => $occupied,
                'vacant' => $vacant,
                'maintenance' => $maintenance,
                'total_units' => $totalUnits,
                'occupied_pct' => $totalUnits > 0 ? (int) round($occupied / $totalUnits * 100) : 0,
                'vacant_pct' => $totalUnits > 0 ? (int) round($vacant / $totalUnits * 100) : 0,
                'maintenance_pct' => $totalUnits > 0 ? (int) round($maintenance / $totalUnits * 100) : 0,
            ],
            'complaint' => [
                'open' => $cOpen,
                'in_progress' => $cProgress,
                'resolved' => $cResolved,
                'total' => $cOpen + $cProgress + $cResolved,
            ],
            'activities' => $activities,
            'notices' => $notices,
            'societyInfo' => [
                'name' => $society->name,
                'address' => trim(collect([$society->address_line_1, $society->city, $society->state])->filter()->implode(', ')) ?: '—',
                'established' => $society->year_established ?: optional($society->registration_date)->format('Y') ?: '—',
                'towers' => $society->wings_count ?: $society->blocks_count ?: '—',
                'floors' => $society->flats_count ?: '—',
            ],
        ];
    }

    /**
     * Round a value up to a clean axis ceiling (1/2/2.5/5 × 10ⁿ).
     */
    private function niceCeil(float $value): float
    {
        if ($value <= 0) {
            return 1;
        }
        $magnitude = 10 ** floor(log10($value));
        foreach ([1, 2, 2.5, 5, 10] as $factor) {
            if ($value <= $factor * $magnitude) {
                return $factor * $magnitude;
            }
        }

        return 10 * $magnitude;
    }

    /**
     * Indian digit-grouping (e.g. 245800 -> 2,45,800).
     */
    private function inr(int|float $num): string
    {
        $num = (string) (int) round($num);
        $negative = str_starts_with($num, '-');
        $num = ltrim($num, '-');
        $last3 = substr($num, -3);
        $rest = substr($num, 0, -3);
        if ($rest !== '') {
            $rest = preg_replace('/\B(?=(\d{2})+(?!\d))/', ',', $rest);
            $num = $rest.','.$last3;
        } else {
            $num = $last3;
        }

        return ($negative ? '-' : '').$num;
    }
}
