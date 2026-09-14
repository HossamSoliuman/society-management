<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreNoticeRequest;
use App\Jobs\DeliverAnnouncement;
use App\Models\Notice;
use App\Models\Role;
use App\Models\Society;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class NoticeController extends Controller
{
    public function index(Request $request): View
    {
        $notices = Notice::query()
            ->with(['creator', 'society'])
            ->withCount('acknowledgements')
            ->when($request->filled('search'), function ($query) use ($request) {
                $search = $request->string('search');
                $query->where(function ($q) use ($search) {
                    $q->where('title', 'like', "%{$search}%")
                        ->orWhere('short_description', 'like', "%{$search}%")
                        ->orWhere('content', 'like', "%{$search}%");
                });
            })
            ->when($request->filled('notice_type'), fn ($q) => $q->where('notice_type', $request->notice_type))
            ->when($request->filled('status'), fn ($q) => $q->where('status', $request->status))
            ->when($request->filled('priority'), fn ($q) => $q->where('priority', $request->priority))
            ->when($request->filled('society'), fn ($q) => $q->where('society_id', $request->integer('society')))
            ->when($request->filled('from'), fn ($q) => $q->whereDate('publish_at', '>=', $request->from))
            ->when($request->filled('to'), fn ($q) => $q->whereDate('publish_at', '<=', $request->to))
            ->latest('publish_at')
            ->paginate(10)
            ->withQueryString();

        $byStatus = Notice::query()->selectRaw('status, COUNT(*) as c')->groupBy('status')->pluck('c', 'status');

        $stats = [
            'total' => (int) $byStatus->sum(),
            'published' => (int) ($byStatus['published'] ?? 0),
            'scheduled' => (int) ($byStatus['scheduled'] ?? 0),
            'drafts' => (int) ($byStatus['draft'] ?? 0),
            'expired' => (int) ($byStatus['expired'] ?? 0),
        ];

        return view('superadmin.notices.index', [
            'notices' => $notices,
            'stats' => $stats,
            'societies' => Society::orderBy('name')->get(['id', 'name']),
        ]);
    }

    public function create(): View
    {
        return view('superadmin.notices.create', [
            'societies' => Society::where('status', 'active')->orderBy('name')->get(['id', 'name']),
            'roles' => Role::whereIn('name', Notice::$audienceRoles)->orderBy('id')->get(['id', 'name', 'display_name']),
        ]);
    }

    public function store(StoreNoticeRequest $request): RedirectResponse
    {
        $validated = $request->validated();

        $validated['society_id'] = $validated['society_id'] ?? null;
        $validated['target_roles'] = array_values(array_filter($validated['target_roles'] ?? [])) ?: null;
        $validated['pin_to_dashboard'] = $request->boolean('pin_to_dashboard');
        $validated['send_email'] = $request->boolean('send_email');
        $validated['send_sms'] = $request->boolean('send_sms');
        $validated['require_acknowledgement'] = $request->boolean('require_acknowledgement');
        $validated['created_by'] = auth()->id();

        $status = $validated['status'] ?? 'published';
        if ($status === 'published' && ! empty($validated['publish_at']) && now()->lt($validated['publish_at'])) {
            $status = 'scheduled';
        }
        $validated['status'] = $status;

        $notice = new Notice($validated);
        $notice->estimated_recipients = $notice->countRecipients();
        $notice->save();

        if ($notice->status === 'published') {
            DeliverAnnouncement::dispatch($notice);
        }

        return redirect()->route('superadmin.notices.index')
            ->with('success', match ($notice->status) {
                'published' => "Notice published and queued for {$notice->estimated_recipients} recipient(s).",
                'scheduled' => "Notice scheduled for {$notice->publish_at->format('d M Y H:i')}.",
                default => 'Notice saved as draft.',
            });
    }
}
