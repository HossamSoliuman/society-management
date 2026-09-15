<?php

namespace App\Http\Controllers\Member;

use App\Models\Notice;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DashboardController extends PortalController
{
    public function index(Request $request): View
    {
        $member = $this->currentMember()->load(['society', 'units']);
        $user = $request->user();

        $openBills = $member->bills()
            ->whereIn('status', ['pending', 'partial', 'overdue'])
            ->orderBy('due_date')
            ->get();

        $recentPayments = $member->payments()
            ->orderByDesc('receipt_date')
            ->orderByDesc('id')
            ->limit(5)
            ->get();

        $openTickets = $member->tickets()
            ->whereIn('status', ['open', 'in_progress'])
            ->orderByDesc('raised_at')
            ->limit(5)
            ->get();

        $notices = Notice::query()
            ->live()
            ->forUser($user)
            ->orderByDesc('pin_to_dashboard')
            ->orderByDesc('publish_at')
            ->limit(5)
            ->get();

        return view('member.dashboard', [
            'member' => $member,
            'society' => $member->society,
            'stats' => [
                'outstanding' => (float) $openBills->sum('outstanding_amount'),
                'overdue' => $openBills->where('status', 'overdue')->count(),
                'paid_this_year' => (float) $member->payments()
                    ->where('status', 'paid')
                    ->whereDate('receipt_date', '>=', now()->startOfYear())
                    ->sum('paid_amount'),
                'open_tickets' => $member->tickets()->whereIn('status', ['open', 'in_progress'])->count(),
            ],
            'openBills' => $openBills,
            'recentPayments' => $recentPayments,
            'openTickets' => $openTickets,
            'notices' => $notices,
        ]);
    }
}
