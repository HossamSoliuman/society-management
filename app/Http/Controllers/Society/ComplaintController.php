<?php

namespace App\Http\Controllers\Society;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreComplaintRequest;
use App\Http\Requests\StoreTicketReplyRequest;
use App\Models\Member;
use App\Models\Society;
use App\Models\SupportTicket;
use App\Models\User;
use App\Services\TicketService;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

/**
 * Complaint desk: resident complaints (tickets raised by or for a member),
 * with assignment to the society team, status and a reply thread.
 */
class ComplaintController extends Controller
{
    public function __construct(private readonly TicketService $tickets) {}

    public function index(Request $request): View
    {
        $society = $this->currentSociety();
        $tab = array_key_exists((string) $request->query('tab'), SupportTicket::STATUSES) ? (string) $request->query('tab') : 'all';

        $complaints = $this->complaints($society)
            ->with(['member', 'assignedTo'])
            ->when($tab !== 'all', fn ($q) => $q->where('status', $tab))
            ->when($request->filled('category'), fn ($q) => $q->where('category', $request->string('category')))
            ->when($request->filled('priority'), fn ($q) => $q->where('priority', $request->string('priority')))
            ->when($request->query('assigned') === 'me', fn ($q) => $q->where('assigned_to', $request->user()->id))
            ->when($request->query('assigned') === 'none', fn ($q) => $q->whereNull('assigned_to'))
            ->when($request->filled('q'), function ($q) use ($request) {
                $term = $request->string('q');
                $q->where(fn ($sub) => $sub->where('ticket_number', 'like', "%{$term}%")
                    ->orWhere('subject', 'like', "%{$term}%")
                    ->orWhere('raised_by_name', 'like', "%{$term}%")
                    ->orWhere('flat_no', 'like', "%{$term}%"));
            })
            ->orderByRaw("CASE WHEN status IN ('open', 'in_progress', 'reopened') THEN 0 ELSE 1 END")
            ->orderByDesc('raised_at')
            ->orderByDesc('id')
            ->paginate(10)
            ->withQueryString();

        return view('society.complaints.index', [
            'complaints' => $complaints,
            'tab' => $tab,
            'tabs' => ['all' => 'All'] + SupportTicket::STATUSES,
            'stats' => $this->stats($society),
            'categories' => SupportController::CATEGORIES,
            'priorities' => SupportController::PRIORITIES,
        ]);
    }

    public function create(): View
    {
        $society = $this->currentSociety();

        return view('society.complaints.create', [
            'members' => Member::query()->forSociety($society)->where('status', 'active')->orderBy('flat_unit')->orderBy('name')->get(['id', 'name', 'flat_unit', 'tower_wing']),
            'team' => $this->team($society),
            'categories' => SupportController::CATEGORIES,
            'priorities' => SupportController::PRIORITIES,
        ]);
    }

    public function store(StoreComplaintRequest $request): RedirectResponse
    {
        $society = $this->currentSociety();
        $data = $request->validated();
        $member = Member::query()->forSociety($society)->findOrFail($data['member_id']);

        $ticket = $this->tickets->create($society, [
            'subject' => $data['subject'],
            'description' => $data['description'],
            'category' => $data['category'],
            'priority' => $data['priority'],
            'location' => $data['location'] ?? null,
            'raised_by_type' => 'member',
            'member_id' => $member->id,
            'raised_by_name' => $member->name,
            'flat_no' => $member->flat_unit,
            'mobile' => $member->mobile,
            'email' => $member->email,
            'attachment_path' => $request->hasFile('attachment') ? $request->file('attachment')->store('support-attachments', 'public') : null,
        ], $request->user(), complaintDesk: true);

        if (! empty($data['assigned_to'])) {
            $this->tickets->assign($ticket, User::find($data['assigned_to']), $request->user());
        }

        return redirect()->route('society.complaints.show', $ticket)
            ->with('success', "Complaint {$ticket->ticket_number} logged for {$member->name}.");
    }

    public function show(SupportTicket $complaint): View
    {
        $this->ensureComplaint($complaint);

        return view('society.complaints.show', [
            'complaint' => $complaint->load(['member', 'assignedTo', 'creator', 'replies.user']),
            'team' => $this->team($this->currentSociety()),
            'statuses' => SupportTicket::STATUSES,
        ]);
    }

    public function reply(StoreTicketReplyRequest $request, SupportTicket $complaint): RedirectResponse
    {
        $this->ensureComplaint($complaint);
        $data = $request->validated();
        $attachment = $request->hasFile('attachment')
            ? $request->file('attachment')->store('support-attachments', 'public')
            : null;

        $this->tickets->reply($complaint, $request->user(), $data['message'], $data['status'] ?? null, $attachment, complaintDesk: true);

        return redirect()->route('society.complaints.show', $complaint)->with('success', 'Reply posted.');
    }

    public function updateStatus(Request $request, SupportTicket $complaint): RedirectResponse
    {
        $this->ensureComplaint($complaint);
        $data = $request->validate(['status' => ['required', Rule::in(array_keys(SupportTicket::STATUSES))]]);

        $this->tickets->changeStatus($complaint, $data['status'], $request->user(), complaintDesk: true);

        return redirect()->route('society.complaints.show', $complaint)
            ->with('success', 'Status changed to '.SupportTicket::STATUSES[$data['status']].'.');
    }

    public function assign(Request $request, SupportTicket $complaint): RedirectResponse
    {
        $this->ensureComplaint($complaint);
        $society = $this->currentSociety();
        $data = $request->validate([
            'assigned_to' => ['nullable', Rule::in($this->team($society)->pluck('id')->all())],
        ]);

        $assignee = ! empty($data['assigned_to']) ? User::find($data['assigned_to']) : null;
        $this->tickets->assign($complaint, $assignee, $request->user());

        return redirect()->route('society.complaints.show', $complaint)
            ->with('success', $assignee ? "Assigned to {$assignee->name}." : 'Assignment cleared.');
    }

    /**
     * @return Builder<SupportTicket>
     */
    private function complaints(Society $society): Builder
    {
        return SupportTicket::query()->forSociety($society)->where('raised_by_type', 'member');
    }

    private function ensureComplaint(SupportTicket $ticket): void
    {
        abort_unless($ticket->raised_by_type === 'member', 404);
    }

    /**
     * Active society team members a complaint can be assigned to.
     *
     * @return Collection<int, User>
     */
    private function team(Society $society): Collection
    {
        return User::query()
            ->where('society_id', $society->id)
            ->where('status', 'active')
            ->whereHas('roles', fn ($q) => $q->whereIn('name', ['society_admin', 'manager', 'staff', 'accountant']))
            ->orderBy('name')
            ->get(['id', 'name']);
    }

    /**
     * @return array<string, int|string>
     */
    private function stats(Society $society): array
    {
        $byStatus = $this->complaints($society)
            ->selectRaw('status, COUNT(*) as c')
            ->groupBy('status')
            ->pluck('c', 'status');

        $resolvedHours = $this->complaints($society)
            ->whereNotNull('resolved_at')
            ->whereNotNull('raised_at')
            ->get(['raised_at', 'resolved_at'])
            ->map(fn (SupportTicket $t) => $t->raised_at->diffInMinutes($t->resolved_at) / 60);

        $avg = $resolvedHours->isNotEmpty() ? $resolvedHours->avg() : null;

        return [
            'open' => (int) (($byStatus['open'] ?? 0) + ($byStatus['reopened'] ?? 0)),
            'in_progress' => (int) ($byStatus['in_progress'] ?? 0),
            'resolved' => (int) (($byStatus['resolved'] ?? 0) + ($byStatus['closed'] ?? 0)),
            'unassigned' => $this->complaints($society)->whereIn('status', ['open', 'in_progress', 'reopened'])->whereNull('assigned_to')->count(),
            'avg_resolution' => $avg === null ? '—' : ($avg >= 24 ? round($avg / 24, 1).' days' : round($avg, 1).' hrs'),
        ];
    }
}
