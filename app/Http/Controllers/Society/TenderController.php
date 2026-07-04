<?php

namespace App\Http\Controllers\Society;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreTenderRequest;
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
        $tenders = $this->baseQuery($request, ['open', 'in_progress'])
            ->orderByDesc('start_date')
            ->orderByDesc('id')
            ->paginate(6)
            ->withQueryString();

        return view('society.tenders.active', [
            'tenders' => $tenders,
            'stats' => [
                ['label' => 'Active Tenders', 'value' => '12', 'sub' => 'Ongoing tenders', 'icon' => 'fa-clipboard-list', 'color' => 'blue'],
                ['label' => 'Draft Tenders', 'value' => '05', 'sub' => 'Under preparation', 'icon' => 'fa-pen-to-square', 'color' => 'orange'],
                ['label' => 'Awarded Tenders', 'value' => '08', 'sub' => 'Successfully awarded', 'icon' => 'fa-trophy', 'color' => 'green'],
                ['label' => 'Closed Tenders', 'value' => '15', 'sub' => 'Completed/closed', 'icon' => 'fa-folder', 'color' => 'purple'],
                ['label' => 'Total Vendors', 'value' => '64', 'sub' => 'Registered vendors', 'icon' => 'fa-users', 'color' => 'red'],
            ],
            'departments' => self::DEPARTMENTS,
            'tenderTypes' => self::TENDER_TYPES,
            'statusOptions' => ['open' => 'Open', 'in_progress' => 'In Progress'],
        ]);
    }

    public function draft(Request $request): View
    {
        $tenders = $this->baseQuery($request, ['draft', 'under_review'])
            ->orderByDesc('updated_at')
            ->orderByDesc('id')
            ->paginate(10)
            ->withQueryString();

        return view('society.tenders.draft', [
            'tenders' => $tenders,
            'stats' => [
                ['label' => 'Total Draft Tenders', 'value' => '05', 'sub' => 'Under preparation', 'icon' => 'fa-pen-to-square', 'color' => 'orange'],
                ['label' => 'Pending Review', 'value' => '03', 'sub' => 'Awaiting internal review', 'icon' => 'fa-calendar-check', 'color' => 'blue'],
                ['label' => 'Expiring Soon', 'value' => '01', 'sub' => 'Within 7 days', 'icon' => 'fa-clock', 'color' => 'green'],
                ['label' => 'Created This Month', 'value' => '05', 'sub' => 'New drafts', 'icon' => 'fa-user-plus', 'color' => 'purple'],
            ],
            'departments' => self::DEPARTMENTS,
            'tenderTypes' => self::TENDER_TYPES,
        ]);
    }

    public function awarded(Request $request): View
    {
        $tenders = $this->baseQuery($request, ['awarded'])
            ->orderByDesc('awarded_date')
            ->orderByDesc('id')
            ->paginate(10)
            ->withQueryString();

        return view('society.tenders.awarded', [
            'tenders' => $tenders,
            'stats' => [
                ['label' => 'Total Awarded Tenders', 'value' => '08', 'sub' => 'Successfully awarded', 'icon' => 'fa-trophy', 'color' => 'green'],
                ['label' => 'Total Contract Value', 'value' => '&#8377; 48,75,600', 'sub' => 'Across all awarded tenders', 'icon' => 'fa-building-columns', 'color' => 'purple'],
                ['label' => 'Awarded This Month', 'value' => '02', 'sub' => 'Newly awarded', 'icon' => 'fa-calendar-check', 'color' => 'orange'],
                ['label' => 'Total Vendors Awarded', 'value' => '06', 'sub' => 'Unique vendors', 'icon' => 'fa-users', 'color' => 'blue'],
            ],
            'departments' => self::DEPARTMENTS,
            'tenderTypes' => self::TENDER_TYPES,
            'vendors' => $this->awardedVendors(),
        ]);
    }

    public function closed(Request $request): View
    {
        $tenders = $this->baseQuery($request, ['closed'])
            ->orderByDesc('closed_date')
            ->orderByDesc('id')
            ->paginate(10)
            ->withQueryString();

        return view('society.tenders.closed', [
            'tenders' => $tenders,
            'stats' => [
                ['label' => 'Total Closed Tenders', 'value' => '15', 'sub' => 'Completed / Closed', 'icon' => 'fa-folder', 'color' => 'purple'],
                ['label' => 'Total Contract Value', 'value' => '&#8377; 1,15,20,450', 'sub' => 'Across all closed tenders', 'icon' => 'fa-file-invoice', 'color' => 'blue'],
                ['label' => 'Completed This Month', 'value' => '03', 'sub' => 'Closed this month', 'icon' => 'fa-circle-check', 'color' => 'green'],
                ['label' => 'Vendors Worked With', 'value' => '11', 'sub' => 'Unique vendors', 'icon' => 'fa-users', 'color' => 'orange'],
            ],
            'departments' => self::DEPARTMENTS,
            'tenderTypes' => self::TENDER_TYPES,
            'vendors' => $this->awardedVendors(),
        ]);
    }

    public function create(): View
    {
        return view('society.tenders.create', [
            'departments' => self::DEPARTMENTS,
            'tenderTypes' => self::TENDER_TYPES,
            'categories' => self::CATEGORIES,
            'paymentTerms' => self::PAYMENT_TERMS,
            'deliveryTerms' => self::DELIVERY_TERMS,
            'contractTypes' => self::CONTRACT_TYPES,
            'taxOptions' => self::TAX_OPTIONS,
            'visibilityOptions' => self::VISIBILITY_OPTIONS,
        ]);
    }

    public function store(StoreTenderRequest $request): RedirectResponse
    {
        $society = $this->currentSociety();
        $data = $request->validated();

        $isDraft = $request->input('action') === 'draft';

        $data['society_id'] = $society?->id;
        $data['reference_no'] = $data['reference_no'] ?? $this->nextReferenceNo();
        $data['status'] = $isDraft ? 'draft' : 'open';
        $data['allow_online_submission'] = $request->boolean('allow_online_submission');
        $data['allow_partial_bidding'] = $request->boolean('allow_partial_bidding');
        $data['created_by'] = $request->user()?->name ?? 'Super Admin';

        $tender = Tender::create($data);

        $route = $isDraft ? 'society.tenders.draft' : 'society.tenders.active';

        return redirect()->route($route)
            ->with('success', "Tender {$tender->reference_no} — {$tender->title} saved successfully.");
    }

    public function reports(): RedirectResponse
    {
        return redirect()->route('society.placeholder', ['page' => 'Tender Reports']);
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
            ->when($society, fn ($q) => $q->where('society_id', $society->id))
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
     * @return array<int, string>
     */
    private function awardedVendors(): array
    {
        $society = $this->currentSociety();

        return Tender::query()
            ->when($society, fn ($q) => $q->where('society_id', $society->id))
            ->whereNotNull('awarded_vendor')
            ->distinct()
            ->orderBy('awarded_vendor')
            ->pluck('awarded_vendor')
            ->all();
    }

    private function nextReferenceNo(): string
    {
        $next = (Tender::max('id') ?? 0) + 1;

        return 'TND-2025-'.str_pad((string) $next, 4, '0', STR_PAD_LEFT);
    }
}
