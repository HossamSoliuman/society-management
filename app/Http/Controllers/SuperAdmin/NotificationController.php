<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Announcement;
use App\Models\Subscription;
use Carbon\Carbon;

class NotificationController extends Controller
{
    public function announcements()
    {
        $announcements = Announcement::with('creator')->latest()->paginate(10);
        return view('superadmin.notification.announcements', compact('announcements'));
    }

    public function createAnnouncement()
    {
        return view('superadmin.notification.create-announcement');
    }

    public function storeAnnouncement(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'message' => 'required|string',
            'recipient_type' => 'required|in:all_members,all_residents,all_staff,custom',
            'priority' => 'required|in:normal,high,urgent',
            'category' => 'nullable|string',
            'delivery_channel' => 'required|in:in_app,email,sms,all',
            'send_type' => 'required|in:now,scheduled',
            'scheduled_at' => 'nullable|date',
        ]);

        $validated['created_by'] = auth()->id();
        $validated['status'] = $request->send_type === 'now' ? 'sent' : 'scheduled';
        $validated['estimated_recipients'] = 320;

        if ($request->send_type === 'now') {
            $validated['sent_at'] = now();
        }

        Announcement::create($validated);

        return redirect()->route('superadmin.notification.announcements')->with('success', 'Announcement created successfully');
    }

    public function renewalAlerts()
    {
        $totalRenewals = Subscription::where('end_date', '<=', now()->addDays(60))
            ->where('status', '!=', 'cancelled')->count();
        $dueIn7Days = Subscription::whereBetween('end_date', [now(), now()->addDays(7)])->count();
        $dueIn30Days = Subscription::whereBetween('end_date', [now()->addDays(8), now()->addDays(30)])->count();
        $dueIn60Days = Subscription::whereBetween('end_date', [now()->addDays(31), now()->addDays(60)])->count();
        $overdue = Subscription::where('end_date', '<', now())->where('status', '!=', 'cancelled')->count();

        $renewals = Subscription::with(['society', 'plan'])
            ->where('end_date', '<=', now()->addDays(60))
            ->where('status', '!=', 'cancelled')
            ->orderBy('end_date')
            ->paginate(10);

        return view('superadmin.notification.renewals', compact(
            'totalRenewals', 'dueIn7Days', 'dueIn30Days', 'dueIn60Days', 'overdue', 'renewals'
        ));
    }
}
