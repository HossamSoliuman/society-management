<?php

namespace App\Http\Controllers\Member;

use App\Http\Controllers\Society\SupportController as SocietySupportController;
use App\Http\Requests\StoreMemberSupportRequest;
use App\Models\SupportTicket;
use App\Services\TicketService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * Complaints / service requests raised by a resident against their society.
 * These are the same tickets the society sees under Priority Support.
 */
class SupportController extends PortalController
{
    public function __construct(private readonly TicketService $tickets) {}

    public function index(Request $request): View
    {
        $member = $this->currentMember();

        $tickets = $member->tickets()
            ->when($request->filled('status'), fn ($q) => $q->where('status', $request->string('status')))
            ->orderByDesc('raised_at')
            ->orderByDesc('id')
            ->paginate(10)
            ->withQueryString();

        return view('member.support.index', [
            'member' => $member,
            'tickets' => $tickets,
            'statuses' => SupportTicket::STATUSES,
            'stats' => [
                'open' => $member->tickets()->where('status', 'open')->count(),
                'in_progress' => $member->tickets()->where('status', 'in_progress')->count(),
                'resolved' => $member->tickets()->whereIn('status', ['resolved', 'closed'])->count(),
            ],
        ]);
    }

    public function create(): View
    {
        return view('member.support.create', [
            'member' => $this->currentMember(),
            'categories' => SocietySupportController::CATEGORIES,
            'priorities' => SocietySupportController::PRIORITIES,
            'contactMethods' => SocietySupportController::CONTACT_METHODS,
        ]);
    }

    public function store(StoreMemberSupportRequest $request): RedirectResponse
    {
        $member = $this->currentMember()->load('society');
        $data = $request->validated();

        $payload = [
            'subject' => $data['subject'],
            'category' => $data['category'],
            'raised_by_type' => 'member',
            'member_id' => $member->id,
            'raised_by_name' => $member->name,
            'flat_no' => $member->flat_unit,
            'mobile' => $member->mobile,
            'email' => $member->email,
            'preferred_contact' => $data['preferred_contact'] ?? null,
            'priority' => $data['priority'],
            'description' => $data['description'],
            'location' => $data['location'] ?? null,
        ];

        if ($request->hasFile('attachment')) {
            $payload['attachment_path'] = $request->file('attachment')->store('support-attachments', 'public');
        }

        $ticket = $this->tickets->create($member->society, $payload, $request->user());

        return redirect()->route('member.support.show', $ticket)
            ->with('success', "Request {$ticket->ticket_number} submitted. The society office has been notified.");
    }

    public function show(SupportTicket $ticket): View
    {
        $this->ownedByMember($ticket->member_id);

        return view('member.support.show', [
            'ticket' => $ticket->load(['replies.user']),
        ]);
    }

    public function reply(Request $request, SupportTicket $ticket): RedirectResponse
    {
        $this->ownedByMember($ticket->member_id);

        $data = $request->validate([
            'message' => ['required', 'string', 'max:2000'],
            'attachment' => ['nullable', 'file', 'mimes:jpg,jpeg,png,pdf', 'max:5120'],
        ]);

        $attachment = $request->hasFile('attachment')
            ? $request->file('attachment')->store('support-attachments', 'public')
            : null;

        // A resident replying to a resolved ticket re-opens it for the office.
        $status = in_array($ticket->status, ['resolved', 'closed'], true) ? 'open' : null;

        $this->tickets->reply($ticket, $request->user(), $data['message'], $status, $attachment);

        return redirect()->route('member.support.show', $ticket)->with('success', 'Reply posted.');
    }
}
