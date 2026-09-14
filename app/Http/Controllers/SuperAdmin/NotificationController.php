<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Jobs\DeliverAnnouncement;
use App\Models\Announcement;
use App\Models\Role;
use App\Models\Society;
use App\Models\Subscription;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class NotificationController extends Controller
{
    public function announcements(): View
    {
        $announcements = Announcement::with(['creator', 'society'])->latest()->paginate(10);

        return view('superadmin.notification.announcements', compact('announcements'));
    }

    public function createAnnouncement(): View
    {
        return view('superadmin.notification.create-announcement', [
            'societies' => Society::where('status', 'active')->orderBy('name')->get(['id', 'name']),
            'roles' => $this->audienceRoles(),
        ]);
    }

    /**
     * AJAX: live recipient count for the current targeting selection.
     */
    public function estimateRecipients(Request $request): JsonResponse
    {
        $custom = array_values(array_filter((array) $request->input('target_roles', [])));
        $recipientType = $request->string('recipient_type')->toString();

        $announcement = new Announcement([
            'society_id' => $request->integer('society_id') ?: null,
            'target_roles' => $recipientType && $recipientType !== 'custom'
                ? $this->rolesFor($recipientType, $custom)
                : ($custom ?: null),
        ]);

        return response()->json(['count' => $announcement->countRecipients()]);
    }

    public function storeAnnouncement(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'message' => 'required|string',
            'society_id' => ['nullable', Rule::exists('societies', 'id')->whereNull('deleted_at')],
            'recipient_type' => 'required|in:all_members,all_residents,all_staff,custom',
            'target_roles' => 'nullable|array',
            'target_roles.*' => [Rule::in(Announcement::$audienceRoles)],
            'priority' => 'required|in:normal,high,urgent',
            'category' => 'nullable|string',
            'delivery_channel' => 'required|in:in_app,email,sms,all',
            'send_type' => 'required|in:now,scheduled',
            'scheduled_at' => 'nullable|date|required_if:send_type,scheduled',
        ]);

        $validated['society_id'] = $validated['society_id'] ?? null;
        $validated['target_roles'] = $this->rolesFor($validated['recipient_type'], $validated['target_roles'] ?? []);
        $validated['created_by'] = auth()->id();
        $validated['status'] = $validated['send_type'] === 'now' ? 'sent' : 'scheduled';

        $announcement = new Announcement($validated);
        $announcement->estimated_recipients = $announcement->countRecipients();

        if ($validated['send_type'] === 'now') {
            $announcement->sent_at = now();
        }

        $announcement->save();

        if ($announcement->send_type === 'now') {
            DeliverAnnouncement::dispatch($announcement);
        }

        return redirect()->route('superadmin.notification.announcements')
            ->with('success', $announcement->send_type === 'now'
                ? "Announcement queued for {$announcement->estimated_recipients} recipient(s)."
                : "Announcement scheduled for {$announcement->scheduled_at->format('d M Y H:i')}.");
    }

    public function renewalAlerts(): View
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

    /**
     * Map the legacy recipient_type presets onto role lists.
     *
     * @param  array<int, string>  $custom
     * @return array<int, string>|null
     */
    private function rolesFor(string $recipientType, array $custom): ?array
    {
        return match ($recipientType) {
            'all_members', 'all_residents' => ['member'],
            'all_staff' => ['society_admin', 'manager', 'accountant', 'staff'],
            'custom' => array_values($custom) ?: null,
            default => null,
        };
    }

    /**
     * @return Collection<int, Role>
     */
    private function audienceRoles()
    {
        return Role::whereIn('name', Announcement::$audienceRoles)->orderBy('id')->get(['id', 'name', 'display_name']);
    }
}
