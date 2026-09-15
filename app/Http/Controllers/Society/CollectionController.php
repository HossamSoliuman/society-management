<?php

namespace App\Http\Controllers\Society;

use App\Http\Controllers\Controller;
use App\Jobs\SendMaintenanceBill;
use App\Models\CollectionPayment;
use App\Models\MaintenanceBill;
use App\Models\Member;
use App\Models\Society;
use App\Models\Unit;
use App\Models\User;
use App\Services\PaymentAllocationService;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class CollectionController extends Controller
{
    /** Aging buckets (label => [from, to] days past due). */
    private const BUCKETS = [
        '0-30' => [0, 30],
        '31-60' => [31, 60],
        '61-90' => [61, 90],
        '90+' => [91, null],
    ];

    private const BUCKET_COLORS = ['0-30' => '#16a34a', '31-60' => '#f59e0b', '61-90' => '#ea580c', '90+' => '#dc2626'];

    public function __construct(private readonly PaymentAllocationService $allocations) {}

    public function index(Request $request): View
    {
        return $this->renderList($request, 'society.collections.index', 'Collections');
    }

    public function online(Request $request): View
    {
        return $this->renderList($request, 'society.collections.online', 'Online Payments', onlineOnly: true);
    }

    /**
     * Shared list renderer for Payment Collection + Online Payments (same table shell).
     */
    private function renderList(Request $request, string $view, string $title, bool $onlineOnly = false): View
    {
        $society = $this->currentSociety();

        $tab = $request->string('tab')->toString() ?: 'all';
        $tabStatus = [
            'received' => 'paid',
            'pending' => 'pending',
            'overdue' => 'overdue',
            'refunded' => 'refunded',
        ][$tab] ?? null;

        $query = CollectionPayment::query()
            ->forSociety($society)
            ->when($onlineOnly, fn ($q) => $q->where('is_online', true))
            ->when($tabStatus, fn ($q) => $q->where('status', $tabStatus))
            ->when($request->filled('status'), fn ($q) => $q->where('status', $request->string('status')))
            ->when($request->filled('mode'), fn ($q) => $q->where('payment_mode', $request->string('mode')))
            ->when($request->filled('collected_by'), fn ($q) => $q->where('collected_by', $request->string('collected_by')))
            ->when($request->filled('from'), fn ($q) => $q->whereDate('receipt_date', '>=', Carbon::parse($request->string('from'))))
            ->when($request->filled('to'), fn ($q) => $q->whereDate('receipt_date', '<=', Carbon::parse($request->string('to'))))
            ->when($request->filled('q'), function ($q) use ($request) {
                $term = $request->string('q');
                $q->where(function ($sub) use ($term) {
                    $sub->where('member_name', 'like', "%{$term}%")
                        ->orWhere('flat_number', 'like', "%{$term}%")
                        ->orWhere('receipt_number', 'like', "%{$term}%");
                });
            })
            ->orderByDesc('receipt_date')
            ->orderByDesc('id');

        $payments = $query->paginate(8)->withQueryString();

        return view($view, [
            'society' => $society,
            'title' => $title,
            'payments' => $payments,
            'tab' => $tab,
            'kpis' => $this->kpis($society),
            'overview' => $this->overviewDonut($society),
            'recent' => $this->recentTransactions($society),
            'collectors' => $this->collectors($society),
        ]);
    }

    public function create(Request $request): View
    {
        $society = $this->currentSociety();

        $members = Member::query()->forSociety($society)->orderBy('name')->get();
        $units = Unit::query()->forSociety($society)->orderBy('unit_number')->get();

        $openBills = MaintenanceBill::query()
            ->forSociety($society)
            ->whereIn('status', ['pending', 'partial', 'overdue'])
            ->where('outstanding_amount', '>', 0)
            ->orderBy('due_date')
            ->get();

        $billPeriods = MaintenanceBill::query()->forSociety($society)->select('bill_month')->distinct()->pluck('bill_month');

        return view('society.collections.create', [
            'society' => $society,
            'members' => $members,
            'units' => $units,
            'openBills' => $openBills,
            'billPeriods' => $billPeriods,
            'selectedBill' => $request->integer('bill') ? $openBills->firstWhere('id', $request->integer('bill')) : null,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $society = $this->currentSociety();

        $data = $request->validate([
            'member_id' => ['nullable', 'integer', Rule::exists('members', 'id')->where('society_id', $society->id)],
            'unit_id' => ['nullable', 'integer', Rule::exists('units', 'id')->where('society_id', $society->id)],
            'member_name' => ['nullable', 'string', 'max:255'],
            'flat_number' => ['nullable', 'string', 'max:255'],
            'unit_label' => ['nullable', 'string', 'max:255'],
            'maintenance_bill_id' => ['nullable', 'integer', Rule::exists('maintenance_bills', 'id')->where('society_id', $society->id)],
            'bill_type' => ['required', 'string', 'max:255'],
            'bill_period' => ['nullable', 'string', 'max:255'],
            'due_date' => ['nullable', 'date'],
            'receipt_date' => ['required', 'date'],
            'total_due' => ['required', 'numeric', 'min:0'],
            'paid_amount' => ['required', 'numeric', 'min:0'],
            'discount' => ['nullable', 'numeric', 'min:0'],
            'fine_penalty' => ['nullable', 'numeric', 'min:0'],
            'payment_mode' => ['required', 'in:cash,upi,card,net_banking,cheque,other'],
            'reference_no' => ['nullable', 'string', 'max:255'],
            'transaction_utr' => ['nullable', 'string', 'max:255'],
            'notes' => ['nullable', 'string'],
            'attachment' => ['nullable', 'file', 'mimes:jpg,jpeg,png,pdf', 'max:5120'],
        ]);

        if ($request->hasFile('attachment')) {
            $data['attachment_path'] = $request->file('attachment')->store('collection-attachments', 'public');
        }

        $payment = $this->allocations->record($society, $data, $request->user()->name);

        if ($payment->maintenance_bill_id && ($bill = $payment->maintenanceBill)) {
            $bill->forceFill(['send_email' => (bool) $bill->member?->email, 'send_sms' => false])->saveQuietly();
            if ($bill->send_email) {
                SendMaintenanceBill::dispatch($bill, 'payment_received');
            }
        }

        $allocated = count($this->allocations->lastAllocations);
        $message = "Payment {$payment->receipt_number} recorded successfully."
            .($allocated > 1 ? " Applied to {$allocated} bills (oldest first)." : '');

        if ($request->boolean('print')) {
            return redirect()->route('society.collections.receipts.show', ['payment' => $payment, 'print' => 1])
                ->with('success', $message);
        }

        return redirect()->route('society.collections.index')->with('success', $message);
    }

    public function pendingDues(Request $request): View
    {
        $society = $this->currentSociety();
        $bucket = $request->string('bucket')->toString() ?: 'all';
        if ($bucket !== 'all' && ! array_key_exists($bucket, self::BUCKETS)) {
            $bucket = 'all';
        }

        $base = MaintenanceBill::query()
            ->forSociety($society)
            ->whereIn('status', ['pending', 'partial', 'overdue'])
            ->where('outstanding_amount', '>', 0);

        $counts = ['all' => (clone $base)->count()];
        foreach (self::BUCKETS as $key => [$from, $to]) {
            $counts[$key] = $this->applyBucket(clone $base, $from, $to)->count();
        }

        $rowsQuery = clone $base;
        if ($bucket !== 'all') {
            [$from, $to] = self::BUCKETS[$bucket];
            $rowsQuery = $this->applyBucket($rowsQuery, $from, $to);
        }

        $rows = $rowsQuery
            ->when($request->filled('q'), function ($q) use ($request) {
                $term = $request->string('q');
                $q->where(fn ($sub) => $sub->where('member_name', 'like', "%{$term}%")->orWhere('flat_number', 'like', "%{$term}%")->orWhere('bill_number', 'like', "%{$term}%"));
            })
            ->orderBy('due_date')
            ->orderBy('id')
            ->paginate(10)
            ->withQueryString()
            ->through(function (MaintenanceBill $bill) {
                $days = max(0, (int) $bill->due_date->diffInDays(Carbon::today(), false));

                return [
                    'id' => $bill->id,
                    'member_name' => $bill->member_name ?: '—',
                    'flat_number' => $bill->flat_number ?: '—',
                    'wing' => $bill->tower_wing ?: '',
                    'bill_number' => $bill->bill_number,
                    'bill_period' => $bill->bill_month,
                    'due_date' => $bill->due_date->format('d M Y'),
                    'total_due' => (float) $bill->total_amount,
                    'paid' => (float) $bill->collected_amount,
                    'balance' => (float) $bill->outstanding_amount,
                    'days_overdue' => $days,
                    'bucket' => $this->bucketFor($days),
                ];
            });

        return view('society.collections.pending-dues', [
            'society' => $society,
            'rows' => $rows,
            'bucket' => $bucket,
            'counts' => $counts,
            'kpis' => $this->pendingDuesKpis($society, clone $base),
            'aging' => $this->duesAgingDonut(clone $base),
        ]);
    }

    /**
     * KPI cards on the collections list.
     *
     * @return array<string, mixed>
     */
    private function kpis(Society $society): array
    {
        $paid = fn () => CollectionPayment::query()->forSociety($society)->whereIn('status', ['paid', 'partial']);

        $monthCollected = (float) $paid()->whereBetween('receipt_date', [now()->startOfMonth(), now()->endOfMonth()])->sum('paid_amount');
        $lastMonth = (float) $paid()->whereBetween('receipt_date', [now()->subMonthNoOverflow()->startOfMonth(), now()->subMonthNoOverflow()->endOfMonth()])->sum('paid_amount');
        $yearCollected = (float) $paid()->whereBetween('receipt_date', [now()->startOfYear(), now()->endOfYear()])->sum('paid_amount');
        $lastYear = (float) $paid()->whereBetween('receipt_date', [now()->subYear()->startOfYear(), now()->subYear()->endOfYear()])->sum('paid_amount');

        $open = MaintenanceBill::query()->forSociety($society)->whereIn('status', ['pending', 'partial'])->where('outstanding_amount', '>', 0);
        $overdue = MaintenanceBill::query()->forSociety($society)->where('status', 'overdue')->where('outstanding_amount', '>', 0);

        $trend = fn (float $now, float $then) => $then > 0 ? round(($now - $then) / $then * 100, 1) : null;

        return [
            'month_collected' => $monthCollected,
            'month_trend' => $trend($monthCollected, $lastMonth),
            'year_collected' => $yearCollected,
            'year_trend' => $trend($yearCollected, $lastYear),
            'pending' => (float) (clone $open)->sum('outstanding_amount'),
            'pending_sub' => $this->unitsMembersLabel(clone $open),
            'overdue' => (float) (clone $overdue)->sum('outstanding_amount'),
            'overdue_sub' => $this->unitsMembersLabel(clone $overdue),
        ];
    }

    /**
     * Collection Overview donut: this year's demand split by state.
     *
     * @return array<string, mixed>
     */
    private function overviewDonut(Society $society): array
    {
        $year = MaintenanceBill::query()->forSociety($society)->whereBetween('bill_date', [now()->startOfYear(), now()->endOfYear()]);
        $collected = (float) (clone $year)->sum('collected_amount');
        $pending = (float) (clone $year)->whereIn('status', ['pending', 'partial'])->sum('outstanding_amount');
        $overdue = (float) (clone $year)->where('status', 'overdue')->sum('outstanding_amount');
        $refunded = (float) CollectionPayment::query()->forSociety($society)->where('status', 'refunded')
            ->whereBetween('receipt_date', [now()->startOfYear(), now()->endOfYear()])->sum('paid_amount');
        $total = $collected + $pending + $overdue + $refunded;
        $pct = fn (float $v) => $total > 0 ? round($v / $total * 100, 1).'%' : '0%';

        return [
            'segments' => [
                ['label' => 'Collected', 'value' => $collected, 'pct' => $pct($collected), 'amount' => '&#8377; '.$this->short($collected), 'color' => '#16a34a'],
                ['label' => 'Pending', 'value' => $pending, 'pct' => $pct($pending), 'amount' => '&#8377; '.$this->short($pending), 'color' => '#f59e0b'],
                ['label' => 'Overdue', 'value' => $overdue, 'pct' => $pct($overdue), 'amount' => '&#8377; '.$this->short($overdue), 'color' => '#dc2626'],
                ['label' => 'Refunded', 'value' => $refunded, 'pct' => $pct($refunded), 'amount' => '&#8377; '.$this->short($refunded), 'color' => '#7c3aed'],
            ],
            'center_value' => '&#8377; '.$this->short($collected),
            'center_label' => 'This Year',
            'total' => '&#8377; '.$this->short($total),
        ];
    }

    /**
     * @return Collection<int, CollectionPayment>
     */
    private function recentTransactions(Society $society): Collection
    {
        return CollectionPayment::query()
            ->forSociety($society)
            ->where('paid_amount', '>', 0)
            ->orderByDesc('receipt_date')
            ->take(3)
            ->get();
    }

    /**
     * Society users who can collect payments (for the "Collected By" filter).
     *
     * @return array<int, string>
     */
    private function collectors(Society $society): array
    {
        $users = User::query()->where('society_id', $society->id)->where('status', 'active')->orderBy('name')->pluck('name')->all();
        $historic = CollectionPayment::query()->forSociety($society)->whereNotNull('collected_by')->distinct()->pluck('collected_by')->all();

        return array_values(array_unique(array_merge($users, $historic)));
    }

    /**
     * @param  Builder<MaintenanceBill>  $open
     * @return array<string, mixed>
     */
    private function pendingDuesKpis(Society $society, $open): array
    {
        $dueThisMonth = (clone $open)->whereBetween('due_date', [now()->startOfMonth(), now()->endOfMonth()]);
        $overdue = (clone $open)->whereDate('due_date', '<', Carbon::today());

        $avgDays = (clone $overdue)->get()->avg(fn (MaintenanceBill $b) => $b->due_date->diffInDays(Carbon::today()));

        return [
            'outstanding' => (float) (clone $open)->sum('outstanding_amount'),
            'outstanding_sub' => $this->unitsMembersLabel(clone $open),
            'due_month' => (float) (clone $dueThisMonth)->sum('outstanding_amount'),
            'due_month_sub' => $this->unitsMembersLabel(clone $dueThisMonth),
            'overdue' => (float) (clone $overdue)->sum('outstanding_amount'),
            'overdue_sub' => $this->unitsMembersLabel(clone $overdue),
            'avg_days' => ($avgDays ? (int) round($avgDays) : 0).' Days',
            'avg_days_sub' => 'As on '.Carbon::today()->format('d M Y'),
        ];
    }

    /**
     * @param  Builder<MaintenanceBill>  $open
     * @return array<string, mixed>
     */
    private function duesAgingDonut($open): array
    {
        $segments = [];
        $total = 0.0;
        $amounts = [];
        foreach (self::BUCKETS as $key => [$from, $to]) {
            $amounts[$key] = (float) $this->applyBucket(clone $open, $from, $to)->sum('outstanding_amount');
            $total += $amounts[$key];
        }
        foreach (self::BUCKETS as $key => $_) {
            $label = $key === '90+' ? '90+ Days' : str_replace('-', ' - ', $key).' Days';
            $segments[] = [
                'label' => $label,
                'value' => $amounts[$key],
                'pct' => ($total > 0 ? round($amounts[$key] / $total * 100, 1) : 0).'%',
                'amount' => '&#8377; '.number_format($amounts[$key]),
                'color' => self::BUCKET_COLORS[$key],
            ];
        }

        return [
            'segments' => $segments,
            'center_value' => '&#8377; '.$this->short($total),
            'center_label' => 'Total Dues',
        ];
    }

    /**
     * Constrain a bills query to an aging bucket (days past due, inclusive).
     *
     * @param  Builder<MaintenanceBill>  $query
     * @return Builder<MaintenanceBill>
     */
    private function applyBucket($query, int $from, ?int $to)
    {
        $today = Carbon::today();

        // days past due = today - due_date, so the bucket [from, to] maps to
        // due_date between (today - to) and (today - from); "0" includes future dues.
        if ($from > 0) {
            $query->whereDate('due_date', '<=', $today->copy()->subDays($from));
        }
        if ($to !== null) {
            $query->whereDate('due_date', '>=', $today->copy()->subDays($to));
        }

        return $query;
    }

    private function bucketFor(int $days): string
    {
        foreach (self::BUCKETS as $key => [$from, $to]) {
            if ($days >= $from && ($to === null || $days <= $to)) {
                return $key;
            }
        }

        return '90+';
    }

    /**
     * "N Units / M Members" helper for stat-card subtitles.
     *
     * @param  Builder<MaintenanceBill>  $query
     */
    private function unitsMembersLabel($query): string
    {
        $units = (clone $query)->whereNotNull('unit_id')->distinct()->count('unit_id');
        $members = (clone $query)->whereNotNull('member_id')->distinct()->count('member_id');

        return "{$units} Units / {$members} Members";
    }

    /**
     * Compact Indian notation (12.5L, 1.2Cr) for donut labels.
     */
    private function short(float $value): string
    {
        return match (true) {
            $value >= 10000000 => number_format($value / 10000000, 2).'Cr',
            $value >= 100000 => number_format($value / 100000, 2).'L',
            default => number_format($value),
        };
    }
}
