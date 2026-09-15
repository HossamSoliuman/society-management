<?php

namespace App\Http\Controllers\Society;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreTenderRequest;
use App\Models\ServiceVendor;
use App\Models\Society;
use App\Models\Tender;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\View\View;

class TenderController extends Controller
{
    private const DEPARTMENTS = ['Maintenance', 'Security', 'Housekeeping', 'Administration', 'Facilities'];

    private const TENDER_TYPES = ['service' => 'Service', 'supply' => 'Supply'];

    private const CATEGORIES = ['Open', 'Limited', 'Single'];

    private const PAYMENT_TERMS = ['Advance Payment', 'Net 30 Days', 'Milestone Based', 'On Completion'];

    private const DELIVERY_TERMS = ['Immediate', 'Within 15 Days', 'Within 30 Days', 'As Per Schedule'];

    private const CONTRACT_TYPES = ['Fixed Price', 'Rate Contract', 'Turnkey'];

    private const TAX_OPTIONS = ['Inclusive of Tax', 'Exclusive of Tax', 'GST Extra'];

    private const VISIBILITY_OPTIONS = ['public' => 'Public', 'invited' => 'Invited Vendors Only'];

    public function active(Request $request): View
    {
        $society = $this->currentSociety();
        $tenders = $this->baseQuery($request, ['open', 'in_progress'])
            ->orderByDesc('start_date')
            ->orderByDesc('id')
            ->paginate(6)
            ->withQueryString();

        $counts = $this->statusCounts($society);

        return view('society.tenders.active', [
            'tenders' => $tenders,
            'stats' => [
                ['label' => 'Active Tenders', 'value' => $this->pad($counts['open'] + $counts['in_progress']), 'sub' => 'Ongoing tenders', 'icon' => 'fa-clipboard-list', 'color' => 'blue'],
                ['label' => 'Draft Tenders', 'value' => $this->pad($counts['draft'] + $counts['under_review']), 'sub' => 'Under preparation', 'icon' => 'fa-pen-to-square', 'color' => 'orange'],
                ['label' => 'Awarded Tenders', 'value' => $this->pad($counts['awarded']), 'sub' => 'Successfully awarded', 'icon' => 'fa-trophy', 'color' => 'green'],
                ['label' => 'Closed Tenders', 'value' => $this->pad($counts['closed']), 'sub' => 'Completed/closed', 'icon' => 'fa-folder', 'color' => 'purple'],
                ['label' => 'Total Vendors', 'value' => $this->pad(ServiceVendor::query()->forSociety($society)->count()), 'sub' => 'Registered vendors', 'icon' => 'fa-users', 'color' => 'red'],
            ],
            'departments' => self::DEPARTMENTS,
            'tenderTypes' => self::TENDER_TYPES,
            'statusOptions' => ['open' => 'Open', 'in_progress' => 'In Progress'],
        ]);
    }

    public function draft(Request $request): View
    {
        $society = $this->currentSociety();
        $tenders = $this->baseQuery($request, ['draft', 'under_review'])
            ->orderByDesc('updated_at')
            ->orderByDesc('id')
            ->paginate(10)
            ->withQueryString();

        $drafts = Tender::query()->forSociety($society)->whereIn('status', ['draft', 'under_review']);

        return view('society.tenders.draft', [
            'tenders' => $tenders,
            'stats' => [
                ['label' => 'Total Draft Tenders', 'value' => $this->pad((clone $drafts)->count()), 'sub' => 'Under preparation', 'icon' => 'fa-pen-to-square', 'color' => 'orange'],
                ['label' => 'Pending Review', 'value' => $this->pad((clone $drafts)->where('status', 'under_review')->count()), 'sub' => 'Awaiting internal review', 'icon' => 'fa-calendar-check', 'color' => 'blue'],
                ['label' => 'Expiring Soon', 'value' => $this->pad((clone $drafts)->whereBetween('submission_deadline', [now(), now()->addDays(7)])->count()), 'sub' => 'Within 7 days', 'icon' => 'fa-clock', 'color' => 'green'],
                ['label' => 'Created This Month', 'value' => $this->pad((clone $drafts)->whereBetween('created_at', [now()->startOfMonth(), now()->endOfMonth()])->count()), 'sub' => 'New drafts', 'icon' => 'fa-user-plus', 'color' => 'purple'],
            ],
            'departments' => self::DEPARTMENTS,
            'tenderTypes' => self::TENDER_TYPES,
        ]);
    }

    public function awarded(Request $request): View
    {
        $society = $this->currentSociety();
        $tenders = $this->baseQuery($request, ['awarded'])
            ->orderByDesc('awarded_date')
            ->orderByDesc('id')
            ->paginate(10)
            ->withQueryString();

        $awarded = Tender::query()->forSociety($society)->where('status', 'awarded');

        return view('society.tenders.awarded', [
            'tenders' => $tenders,
            'stats' => [
                ['label' => 'Total Awarded Tenders', 'value' => $this->pad((clone $awarded)->count()), 'sub' => 'Successfully awarded', 'icon' => 'fa-trophy', 'color' => 'green'],
                ['label' => 'Total Contract Value', 'value' => '&#8377; '.number_format((float) (clone $awarded)->sum('contract_value')), 'sub' => 'Across all awarded tenders', 'icon' => 'fa-building-columns', 'color' => 'purple'],
                ['label' => 'Awarded This Month', 'value' => $this->pad((clone $awarded)->whereBetween('awarded_date', [now()->startOfMonth(), now()->endOfMonth()])->count()), 'sub' => 'Newly awarded', 'icon' => 'fa-calendar-check', 'color' => 'orange'],
                ['label' => 'Total Vendors Awarded', 'value' => $this->pad((clone $awarded)->whereNotNull('awarded_vendor')->distinct()->count('awarded_vendor')), 'sub' => 'Unique vendors', 'icon' => 'fa-users', 'color' => 'blue'],
            ],
            'departments' => self::DEPARTMENTS,
            'tenderTypes' => self::TENDER_TYPES,
            'vendors' => $this->awardedVendors(),
        ]);
    }

    public function closed(Request $request): View
    {
        $society = $this->currentSociety();
        $tenders = $this->baseQuery($request, ['closed'])
            ->orderByDesc('closed_date')
            ->orderByDesc('id')
            ->paginate(10)
            ->withQueryString();

        $closed = Tender::query()->forSociety($society)->where('status', 'closed');

        return view('society.tenders.closed', [
            'tenders' => $tenders,
            'stats' => [
                ['label' => 'Total Closed Tenders', 'value' => $this->pad((clone $closed)->count()), 'sub' => 'Completed / Closed', 'icon' => 'fa-folder', 'color' => 'purple'],
                ['label' => 'Total Contract Value', 'value' => '&#8377; '.number_format((float) (clone $closed)->sum('contract_value')), 'sub' => 'Across all closed tenders', 'icon' => 'fa-file-invoice', 'color' => 'blue'],
                ['label' => 'Completed This Month', 'value' => $this->pad((clone $closed)->whereBetween('closed_date', [now()->startOfMonth(), now()->endOfMonth()])->count()), 'sub' => 'Closed this month', 'icon' => 'fa-circle-check', 'color' => 'green'],
                ['label' => 'Vendors Worked With', 'value' => $this->pad((clone $closed)->whereNotNull('awarded_vendor')->distinct()->count('awarded_vendor')), 'sub' => 'Unique vendors', 'icon' => 'fa-users', 'color' => 'orange'],
            ],
            'departments' => self::DEPARTMENTS,
            'tenderTypes' => self::TENDER_TYPES,
            'vendors' => $this->awardedVendors(),
        ]);
    }

    public function create(): View
    {
        return view('society.tenders.create', $this->formOptions());
    }

    public function store(StoreTenderRequest $request): RedirectResponse
    {
        $society = $this->currentSociety();
        $data = $request->validated();

        $isDraft = $request->input('action') === 'draft';

        $data['society_id'] = $society->id;
        $data['reference_no'] = $data['reference_no'] ?? $this->nextReferenceNo($society);
        $data['status'] = $isDraft ? 'draft' : 'open';
        $data['allow_online_submission'] = $request->boolean('allow_online_submission');
        $data['allow_partial_bidding'] = $request->boolean('allow_partial_bidding');
        $data['created_by'] = $request->user()->name;

        $tender = Tender::create($data);

        $route = $isDraft ? 'society.tenders.draft' : 'society.tenders.active';

        return redirect()->route($route)
            ->with('success', "Tender {$tender->reference_no} — {$tender->title} saved successfully.");
    }

    public function show(Tender $tender): View
    {
        return view('society.tenders.show', [
            'tender' => $tender,
            'vendors' => ServiceVendor::query()->forSociety($this->currentSociety())->where('status', 'active')->orderBy('name')->get(),
        ]);
    }

    public function edit(Tender $tender): View
    {
        return view('society.tenders.create', ['tender' => $tender] + $this->formOptions());
    }

    public function update(StoreTenderRequest $request, Tender $tender): RedirectResponse
    {
        $data = $request->validated();
        $data['allow_online_submission'] = $request->boolean('allow_online_submission');
        $data['allow_partial_bidding'] = $request->boolean('allow_partial_bidding');

        if ($tender->status === 'draft' && $request->input('action') === 'publish') {
            $data['status'] = 'open';
        }

        $tender->update($data);

        return redirect()->route('society.tenders.show', $tender)
            ->with('success', "Tender {$tender->reference_no} updated.");
    }

    public function publish(Tender $tender): RedirectResponse
    {
        abort_unless(in_array($tender->status, ['draft', 'under_review'], true), 422, 'Only draft tenders can be published.');

        $tender->update(['status' => 'open', 'start_date' => $tender->start_date ?? Carbon::today()]);

        return redirect()->route('society.tenders.show', $tender)->with('success', "Tender {$tender->reference_no} is now active.");
    }

    public function award(Request $request, Tender $tender): RedirectResponse
    {
        abort_unless(in_array($tender->status, ['open', 'in_progress', 'under_review'], true), 422, 'Only active tenders can be awarded.');

        $data = $request->validate([
            'awarded_vendor' => ['required', 'string', 'max:255'],
            'contract_value' => ['required', 'numeric', 'min:0'],
            'awarded_date' => ['nullable', 'date'],
        ]);

        $tender->update([
            'status' => 'awarded',
            'awarded_vendor' => $data['awarded_vendor'],
            'contract_value' => $data['contract_value'],
            'awarded_date' => $data['awarded_date'] ?? Carbon::today(),
        ]);

        return redirect()->route('society.tenders.show', $tender)
            ->with('success', "Tender {$tender->reference_no} awarded to {$tender->awarded_vendor}.");
    }

    public function close(Request $request, Tender $tender): RedirectResponse
    {
        abort_if($tender->status === 'closed', 422, 'This tender is already closed.');

        $data = $request->validate(['closed_reason' => ['nullable', 'string', 'max:255']]);

        $tender->update([
            'status' => 'closed',
            'closed_date' => Carbon::today(),
            'closed_reason' => $data['closed_reason'] ?? ($tender->status === 'awarded' ? 'Completed' : 'Cancelled'),
        ]);

        return redirect()->route('society.tenders.show', $tender)->with('success', "Tender {$tender->reference_no} closed.");
    }

    public function reports(Request $request): View
    {
        $society = $this->currentSociety();
        $from = $request->filled('from') ? Carbon::parse($request->string('from')) : now()->startOfYear();
        $to = $request->filled('to') ? Carbon::parse($request->string('to')) : now()->endOfYear();

        $base = Tender::query()->forSociety($society)->whereBetween('created_at', [$from->copy()->startOfDay(), $to->copy()->endOfDay()]);

        $byStatus = (clone $base)->selectRaw('status, COUNT(*) as c, COALESCE(SUM(estimated_value),0) as estimated, COALESCE(SUM(contract_value),0) as contract')
            ->groupBy('status')->get()->keyBy('status');
        $byDepartment = (clone $base)->selectRaw('department, COUNT(*) as c, COALESCE(SUM(contract_value),0) as contract')
            ->groupBy('department')->orderByDesc('c')->get();
        $byVendor = (clone $base)->whereNotNull('awarded_vendor')
            ->selectRaw('awarded_vendor, COUNT(*) as c, COALESCE(SUM(contract_value),0) as contract')
            ->groupBy('awarded_vendor')->orderByDesc('contract')->get();

        return view('society.tenders.reports', [
            'from' => $from->toDateString(),
            'to' => $to->toDateString(),
            'total' => (clone $base)->count(),
            'byStatus' => $byStatus,
            'byDepartment' => $byDepartment,
            'byVendor' => $byVendor,
            'estimated' => (float) (clone $base)->sum('estimated_value'),
            'contracted' => (float) (clone $base)->whereIn('status', ['awarded', 'closed'])->sum('contract_value'),
            'recent' => (clone $base)->orderByDesc('created_at')->limit(10)->get(),
        ]);
    }

    /* -------------------------------------------------------------------------
     |  Query helpers
     |------------------------------------------------------------------------- */

    /**
     * Shared list query with the common filters applied.
     *
     * @param  array<int, string>  $statuses
     * @return Builder<Tender>
     */
    private function baseQuery(Request $request, array $statuses): Builder
    {
        $society = $this->currentSociety();

        return Tender::query()
            ->forSociety($society)
            ->whereIn('status', $statuses)
            ->when($request->filled('department'), fn ($q) => $q->where('department', $request->string('department')))
            ->when($request->filled('type'), fn ($q) => $q->where('tender_type', $request->string('type')))
            ->when($request->filled('status'), fn ($q) => $q->where('status', $request->string('status')))
            ->when($request->filled('vendor'), fn ($q) => $q->where('awarded_vendor', $request->string('vendor')))
            ->when($request->filled('from'), fn ($q) => $q->whereDate('start_date', '>=', Carbon::parse($request->string('from'))))
            ->when($request->filled('to'), fn ($q) => $q->whereDate('start_date', '<=', Carbon::parse($request->string('to'))))
            ->when($request->filled('q'), function ($q) use ($request) {
                $term = $request->string('q');
                $q->where(function ($sub) use ($term) {
                    $sub->where('title', 'like', "%{$term}%")
                        ->orWhere('reference_no', 'like', "%{$term}%")
                        ->orWhere('department', 'like', "%{$term}%")
                        ->orWhere('awarded_vendor', 'like', "%{$term}%");
                });
            });
    }

    /**
     * @return array<string, int>
     */
    private function statusCounts(Society $society): array
    {
        $rows = Tender::query()->forSociety($society)->selectRaw('status, COUNT(*) as c')->groupBy('status')->pluck('c', 'status');

        return array_map(fn ($s) => (int) ($rows[$s] ?? 0), array_combine(
            ['open', 'in_progress', 'draft', 'under_review', 'awarded', 'closed'],
            ['open', 'in_progress', 'draft', 'under_review', 'awarded', 'closed'],
        ));
    }

    /**
     * @return array<int, string>
     */
    private function awardedVendors(): array
    {
        return Tender::query()
            ->forSociety($this->currentSociety())
            ->whereNotNull('awarded_vendor')
            ->distinct()
            ->orderBy('awarded_vendor')
            ->pluck('awarded_vendor')
            ->all();
    }

    /**
     * @return array<string, mixed>
     */
    private function formOptions(): array
    {
        return [
            'departments' => self::DEPARTMENTS,
            'tenderTypes' => self::TENDER_TYPES,
            'categories' => self::CATEGORIES,
            'paymentTerms' => self::PAYMENT_TERMS,
            'deliveryTerms' => self::DELIVERY_TERMS,
            'contractTypes' => self::CONTRACT_TYPES,
            'taxOptions' => self::TAX_OPTIONS,
            'visibilityOptions' => self::VISIBILITY_OPTIONS,
        ];
    }

    private function nextReferenceNo(Society $society): string
    {
        $next = (int) Tender::query()->forSociety($society)->count() + 1;
        do {
            $ref = 'TND-'.now()->format('Y').'-'.str_pad((string) $next, 4, '0', STR_PAD_LEFT);
            $next++;
        } while (Tender::query()->forSociety($society)->where('reference_no', $ref)->exists());

        return $ref;
    }

    private function pad(int $value): string
    {
        return str_pad((string) $value, 2, '0', STR_PAD_LEFT);
    }
}
