<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\SupportTicket;
use App\Models\Society;

class SupportTicketController extends Controller
{
    public function index()
    {
        $tickets = SupportTicket::with(['society', 'creator', 'assignedTo'])->latest()->paginate(10);
        $openCount = SupportTicket::where('status', 'open')->count();
        $inProgressCount = SupportTicket::where('status', 'in_progress')->count();
        $resolvedCount = SupportTicket::where('status', 'resolved')->count();

        return view('superadmin.ticket.index', compact('tickets', 'openCount', 'inProgressCount', 'resolvedCount'));
    }

    public function show(SupportTicket $ticket)
    {
        $ticket->load(['society', 'creator', 'assignedTo', 'replies.user']);
        return view('superadmin.ticket.show', compact('ticket'));
    }

    public function updateStatus(Request $request, SupportTicket $ticket)
    {
        $validated = $request->validate([
            'status' => 'required|in:open,in_progress,resolved,closed,reopened',
        ]);

        $ticket->update($validated);

        return redirect()->back()->with('success', 'Ticket status updated');
    }
}
