<?php

namespace App\Http\Controllers\Society;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreSupportRequest;
use App\Models\Member;
use App\Models\Society;
use App\Models\SupportRequest;
use App\Models\Unit;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\View\View;

class SupportController extends Controller
{
    /** Request categories for the filter and the Raise New Request form. */
    private const CATEGORIES = [
        'Maintenance', 'Lift', 'Electrical', 'Housekeeping',
        'Security', 'Garden', 'Access Control', 'Others',
    ];

    private const PRIORITIES = ['high' => 'High', 'medium' => 'Medium', 'low' => 'Low'];

    private const STATUSES = ['open' => 'Open', 'in_progress' => 'In Progress', 'resolved' => 'Resolved', 'closed' => 'Closed'];

    private const CONTACT_METHODS = ['Phone', 'Email', 'WhatsApp', 'SMS'];

    public function index(Request $request): View
    {
        $society = Society::first();

        $tab = $request->string('tab')->toString() ?: 'all';
        $tabStatus = in_array($tab, ['open', 'in_progress', 'resolved', 'closed'], true) ? $tab : null;

        $requests = SupportRequest::query()
            ->when($society, fn ($q) => $q->where('society_id', $society->id))
            ->when($tabStatus, fn ($q) => $q->where('status', $tabStatus))
            ->when($request->filled('status'), fn ($q) => $q->where('status', $request->string('status')))
            ->when($request->filled('priority'), fn ($q) => $q->where('priority', $request->string('priority')))
            ->when($request->filled('category'), fn ($q) => $q->where('category', $request->string('category')))
            ->when($request->filled('raised_by'), fn ($q) => $q->where('raised_by_type', $request->string('raised_by')))
            ->when($request->filled('from'), fn ($q) => $q->whereDate('raised_at', '>=', Carbon::parse($request->string('from'))))
            ->when($request->filled('to'), fn ($q) => $q->whereDate('raised_at', '<=', Carbon::parse($request->string('to'))))
            ->when($request->filled('q'), function ($q) use ($request) {
                $term = $request->string('q');
                $q->where(function ($sub) use ($term) {
                    $sub->where('request_id', 'like', "%{$term}%")
                        ->orWhere('subject', 'like', "%{$term}%")
                        ->orWhere('raised_by_name', 'like', "%{$term}%")
                        ->orWhere('mobile', 'like', "%{$term}%");
                });
            })
            ->orderByDesc('raised_at')
            ->orderByDesc('id')
            ->paginate(8)
            ->withQueryString();

        return view('society.support.index', [
            'society' => $society,
            'requests' => $requests,
            'tab' => $tab,
            'tabs' => ['all' => 'All Requests'] + self::STATUSES,
            'stats' => $this->stats(),
            'categoryDonut' => $this->categoryDonut(),
            'priorityBars' => $this->priorityBars(),
            'categories' => self::CATEGORIES,
            'priorities' => self::PRIORITIES,
            'statuses' => self::STATUSES,
        ]);
    }

    public function create(): View
    {
        $society = Society::first();

        return view('society.support.create', [
            'society' => $society,
            'categories' => self::CATEGORIES,
            'priorities' => self::PRIORITIES,
            'contactMethods' => self::CONTACT_METHODS,
            'members' => $this->members($society),
            'units' => $this->units($society),
        ]);
    }

    public function store(StoreSupportRequest $request): RedirectResponse
    {
        $society = Society::first();
        $data = $request->validated();

        $name = $data['raised_by_name'] ?? null;
        if (! $name && ! empty($data['member_id'])) {
            $name = Member::find($data['member_id'])?->name;
        }

        $payload = [
            'society_id' => $society?->id,
            'request_id' => $this->nextRequestId(),
            'subject' => $data['subject'],
            'category' => $data['category'],
            'raised_by_type' => $data['raised_by_type'],
            'member_id' => $data['member_id'] ?? null,
            'raised_by_name' => $name ?? 'Society Admin',
            'flat_no' => $data['flat_no'] ?? null,
            'mobile' => $data['mobile'] ?? null,
            'email' => $data['email'] ?? null,
            'preferred_contact' => $data['preferred_contact'] ?? null,
            'priority' => $data['priority'],
            'status' => 'open',
            'description' => $data['description'],
            'location' => $data['location'] ?? null,
            'notes' => $data['notes'] ?? null,
            'raised_at' => now(),
        ];

        if ($request->hasFile('attachment')) {
            $payload['attachment_path'] = $request->file('attachment')->store('support-attachments', 'public');
        }

        $supportRequest = SupportRequest::create($payload);

        return redirect()->route('society.support.index')
            ->with('success', "Request {$supportRequest->request_id} submitted successfully.");
    }

    public function show(SupportRequest $request): View
    {
        return view('society.support.show', [
            'request' => $request->load('member'),
        ]);
    }

    private function nextRequestId(): string
    {
        $year = now()->format('Y');
        $next = (SupportRequest::max('id') ?? 0) + 1;

        return 'PS-'.$year.'-'.str_pad((string) $next, 3, '0', STR_PAD_LEFT);
    }

    /**
     * @return Collection<int, Member>
     */
    private function members(?Society $society): Collection
    {
        return Member::query()
            ->when($society, fn ($q) => $q->where('society_id', $society->id))
            ->orderBy('name')
            ->get();
    }

    /**
     * @return Collection<int, Unit>
     */
    private function units(?Society $society): Collection
    {
        return Unit::query()
            ->when($society, fn ($q) => $q->where('society_id', $society->id))
            ->orderBy('unit_number')
            ->get();
    }

    /**
     * Demo stat-card figures matching "Priority support.png".
     *
     * @return array<string, mixed>
     */
    private function stats(): array
    {
        return [
            'total' => 48,
            'total_trend' => '12% vs last month',
            'open' => 12,
            'open_pct' => '25% of total',
            'in_progress' => 8,
            'in_progress_pct' => '16.7% of total',
            'resolved' => 25,
            'resolved_pct' => '52% of total',
            'closed' => 3,
            'closed_pct' => '6.3% of total',
        ];
    }

    /**
     * "Requests by Category (This Month)" donut + legend.
     *
     * @return array<string, mixed>
     */
    private function categoryDonut(): array
    {
        return [
            'center_value' => '48',
            'center_label' => 'Total',
            'segments' => [
                ['label' => 'Maintenance', 'value' => 16, 'pct' => '33.3%', 'color' => '#F97316'],
                ['label' => 'Lift', 'value' => 8, 'pct' => '16.7%', 'color' => '#8B5CF6'],
                ['label' => 'Electrical', 'value' => 6, 'pct' => '12.5%', 'color' => '#10B981'],
                ['label' => 'Housekeeping', 'value' => 6, 'pct' => '12.5%', 'color' => '#EC4899'],
                ['label' => 'Security', 'value' => 5, 'pct' => '10.4%', 'color' => '#EF4444'],
                ['label' => 'Others', 'value' => 7, 'pct' => '14.6%', 'color' => '#94a3b8'],
            ],
        ];
    }

    /**
     * "Priority Distribution" progress bars.
     *
     * @return array<int, array<string, mixed>>
     */
    private function priorityBars(): array
    {
        return [
            ['label' => 'High', 'value' => 20, 'pct' => '41.7%', 'width' => 42, 'color' => 'var(--danger)'],
            ['label' => 'Medium', 'value' => 18, 'pct' => '37.5%', 'width' => 38, 'color' => 'var(--orange)'],
            ['label' => 'Low', 'value' => 10, 'pct' => '20.8%', 'width' => 21, 'color' => 'var(--success)'],
        ];
    }
}
