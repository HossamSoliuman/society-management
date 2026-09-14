<?php

namespace App\Http\Controllers\Society;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreSupportRequest;
use App\Http\Requests\StoreTicketReplyRequest;
use App\Models\Member;
use App\Models\Society;
use App\Models\SupportTicket;
use App\Models\Unit;
use App\Services\TicketService;
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
        'Security', 'Garden', 'Access Control', 'Billing', 'Technical', 'Others',
    ];

    private const PRIORITIES = ['high' => 'High', 'medium' => 'Medium', 'low' => 'Low'];

    private const CONTACT_METHODS = ['Phone', 'Email', 'WhatsApp', 'SMS'];

    /** Colours for the category donut, keyed by category. */
    private const CATEGORY_COLORS = [
        'Maintenance' => '#F97316', 'Lift' => '#8B5CF6', 'Electrical' => '#10B981', 'Housekeeping' => '#EC4899',
        'Security' => '#EF4444', 'Garden' => '#22C55E', 'Access Control' => '#14B8A6', 'Billing' => '#3B82F6',
        'Technical' => '#6366F1', 'Others' => '#94a3b8',
    ];

    public function __construct(private readonly TicketService $tickets) {}

    public function index(Request $request): View
    {
        $society = $this->currentSociety();

        $tab = $request->string('tab')->toString() ?: 'all';
        $tabStatus = array_key_exists($tab, SupportTicket::STATUSES) ? $tab : null;

        $requests = SupportTicket::query()
            ->forSociety($society)
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
                    $sub->where('ticket_number', 'like', "%{$term}%")
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
            'tabs' => ['all' => 'All Requests'] + SupportTicket::STATUSES,
            'stats' => $this->stats($society),
            'categoryDonut' => $this->categoryDonut($society),
            'priorityBars' => $this->priorityBars($society),
            'categories' => self::CATEGORIES,
            'priorities' => self::PRIORITIES,
            'statuses' => SupportTicket::STATUSES,
        ]);
    }

    public function create(): View
    {
        $society = $this->currentSociety();

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
        $society = $this->currentSociety();
        $data = $request->validated();

        $name = $data['raised_by_name'] ?? null;
        if (! $name && ! empty($data['member_id'])) {
            $name = Member::find($data['member_id'])?->name;
        }

        $payload = [
            'subject' => $data['subject'],
            'category' => $data['category'],
            'raised_by_type' => $data['raised_by_type'],
            'member_id' => $data['member_id'] ?? null,
            'raised_by_name' => $name ?? $request->user()->name,
            'flat_no' => $data['flat_no'] ?? null,
            'mobile' => $data['mobile'] ?? null,
            'email' => $data['email'] ?? null,
            'preferred_contact' => $data['preferred_contact'] ?? null,
            'priority' => $data['priority'],
            'description' => $data['description'],
            'location' => $data['location'] ?? null,
            'notes' => $data['notes'] ?? null,
        ];

        if ($request->hasFile('attachment')) {
            $payload['attachment_path'] = $request->file('attachment')->store('support-attachments', 'public');
        }

        $ticket = $this->tickets->create($society, $payload, $request->user());

        return redirect()->route('society.support.index')
            ->with('success', "Request {$ticket->ticket_number} submitted successfully.");
    }

    public function show(SupportTicket $request): View
    {
        return view('society.support.show', [
            'request' => $request->load(['member', 'replies.user']),
            'statuses' => SupportTicket::STATUSES,
        ]);
    }

    public function reply(StoreTicketReplyRequest $form, SupportTicket $request): RedirectResponse
    {
        $data = $form->validated();
        $attachment = $form->hasFile('attachment')
            ? $form->file('attachment')->store('support-attachments', 'public')
            : null;

        $this->tickets->reply($request, $form->user(), $data['message'], $data['status'] ?? null, $attachment);

        return redirect()->route('society.support.show', $request)->with('success', 'Reply posted.');
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
     * Stat-card figures: totals by status plus month-over-month trend.
     *
     * @return array<string, mixed>
     */
    private function stats(Society $society): array
    {
        $byStatus = SupportTicket::query()
            ->forSociety($society)
            ->selectRaw('status, COUNT(*) as c')
            ->groupBy('status')
            ->pluck('c', 'status');

        $total = (int) $byStatus->sum();
        $pct = fn (int $n) => $total > 0 ? round($n / $total * 100, 1).'% of total' : '0% of total';

        $thisMonth = SupportTicket::query()->forSociety($society)
            ->whereBetween('raised_at', [now()->startOfMonth(), now()->endOfMonth()])->count();
        $lastMonth = SupportTicket::query()->forSociety($society)
            ->whereBetween('raised_at', [now()->subMonthNoOverflow()->startOfMonth(), now()->subMonthNoOverflow()->endOfMonth()])->count();
        $trend = $lastMonth > 0
            ? round(($thisMonth - $lastMonth) / $lastMonth * 100).'% vs last month'
            : "{$thisMonth} this month";

        $open = (int) (($byStatus['open'] ?? 0) + ($byStatus['reopened'] ?? 0));
        $inProgress = (int) ($byStatus['in_progress'] ?? 0);
        $resolved = (int) ($byStatus['resolved'] ?? 0);
        $closed = (int) ($byStatus['closed'] ?? 0);

        return [
            'total' => $total,
            'total_trend' => $trend,
            'open' => $open,
            'open_pct' => $pct($open),
            'in_progress' => $inProgress,
            'in_progress_pct' => $pct($inProgress),
            'resolved' => $resolved,
            'resolved_pct' => $pct($resolved),
            'closed' => $closed,
            'closed_pct' => $pct($closed),
        ];
    }

    /**
     * "Requests by Category (This Month)" donut + legend.
     *
     * @return array<string, mixed>
     */
    private function categoryDonut(Society $society): array
    {
        $rows = SupportTicket::query()
            ->forSociety($society)
            ->whereBetween('raised_at', [now()->startOfMonth(), now()->endOfMonth()])
            ->selectRaw('category, COUNT(*) as c')
            ->groupBy('category')
            ->orderByDesc('c')
            ->pluck('c', 'category');

        if ($rows->isEmpty()) {
            $rows = SupportTicket::query()
                ->forSociety($society)
                ->selectRaw('category, COUNT(*) as c')
                ->groupBy('category')
                ->orderByDesc('c')
                ->pluck('c', 'category');
        }

        $total = (int) $rows->sum();
        $segments = [];
        foreach ($rows as $category => $count) {
            $segments[] = [
                'label' => $category ?: 'Others',
                'value' => (int) $count,
                'pct' => $total > 0 ? round($count / $total * 100, 1).'%' : '0%',
                'color' => self::CATEGORY_COLORS[$category] ?? '#94a3b8',
            ];
        }

        return [
            'center_value' => (string) $total,
            'center_label' => 'Total',
            'segments' => $segments,
        ];
    }

    /**
     * "Priority Distribution" progress bars.
     *
     * @return array<int, array<string, mixed>>
     */
    private function priorityBars(Society $society): array
    {
        $rows = SupportTicket::query()
            ->forSociety($society)
            ->selectRaw('priority, COUNT(*) as c')
            ->groupBy('priority')
            ->pluck('c', 'priority');
        $total = (int) $rows->sum();

        $bars = [];
        foreach ([['high', 'High', 'var(--danger)'], ['medium', 'Medium', 'var(--orange)'], ['low', 'Low', 'var(--success)']] as [$key, $label, $color]) {
            $count = (int) ($rows[$key] ?? 0) + ($key === 'high' ? (int) ($rows['urgent'] ?? 0) : 0);
            $width = $total > 0 ? (int) round($count / $total * 100) : 0;
            $bars[] = [
                'label' => $label,
                'value' => $count,
                'pct' => ($total > 0 ? round($count / $total * 100, 1) : 0).'%',
                'width' => $width,
                'color' => $color,
            ];
        }

        return $bars;
    }
}
