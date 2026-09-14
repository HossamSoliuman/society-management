<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreTicketReplyRequest;
use App\Models\Society;
use App\Models\SupportTicket;
use App\Services\TicketService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class SupportTicketController extends Controller
{
    public function __construct(private readonly TicketService $tickets) {}

    public function index(Request $request): View
    {
        $tickets = SupportTicket::with(['society', 'creator', 'assignedTo'])
            ->when($request->filled('status'), fn ($q) => $q->where('status', $request->string('status')))
            ->when($request->filled('priority'), fn ($q) => $q->where('priority', $request->string('priority')))
            ->when($request->filled('society'), fn ($q) => $q->where('society_id', $request->integer('society')))
            ->when($request->filled('q'), function ($q) use ($request) {
                $term = $request->string('q');
                $q->where(fn ($sub) => $sub->where('ticket_number', 'like', "%{$term}%")->orWhere('subject', 'like', "%{$term}%"));
            })
            ->latest('raised_at')
            ->latest('id')
            ->paginate(10)
            ->withQueryString();

        $openCount = SupportTicket::whereIn('status', ['open', 'reopened'])->count();
        $inProgressCount = SupportTicket::where('status', 'in_progress')->count();
        $resolvedCount = SupportTicket::whereIn('status', ['resolved', 'closed'])->count();
        $societies = Society::orderBy('name')->get(['id', 'name']);

        return view('superadmin.ticket.index', compact('tickets', 'openCount', 'inProgressCount', 'resolvedCount', 'societies'));
    }

    public function show(SupportTicket $ticket): View
    {
        $ticket->load(['society', 'creator', 'assignedTo', 'member', 'replies.user']);

        return view('superadmin.ticket.show', compact('ticket'));
    }

    public function updateStatus(Request $request, SupportTicket $ticket): RedirectResponse
    {
        $validated = $request->validate([
            'status' => 'required|in:open,in_progress,resolved,closed,reopened',
        ]);

        $this->tickets->changeStatus($ticket, $validated['status'], $request->user());

        return redirect()->back()->with('success', 'Ticket status updated');
    }

    public function reply(StoreTicketReplyRequest $request, SupportTicket $ticket): RedirectResponse
    {
        $data = $request->validated();
        $attachment = $request->hasFile('attachment')
            ? $request->file('attachment')->store('support-attachments', 'public')
            : null;

        $this->tickets->reply($ticket, $request->user(), $data['message'], $data['status'] ?? null, $attachment);

        return redirect()->route('superadmin.tickets.show', $ticket)->with('success', 'Reply sent to the society.');
    }
}
