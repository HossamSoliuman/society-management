<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreNoticeRequest;
use App\Models\Notice;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class NoticeController extends Controller
{
    public function index(Request $request): View
    {
        $notices = Notice::query()
            ->with('creator')
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
            ->when($request->filled('from'), fn ($q) => $q->whereDate('publish_at', '>=', $request->from))
            ->when($request->filled('to'), fn ($q) => $q->whereDate('publish_at', '<=', $request->to))
            ->latest('publish_at')
            ->paginate(10)
            ->withQueryString();

        // Figures hard-coded to match the design (demo build).
        $stats = [
            'total' => 128,
            'published' => 98,
            'scheduled' => 12,
            'drafts' => 8,
            'expired' => 10,
        ];

        return view('superadmin.notices.index', compact('notices', 'stats'));
    }

    public function create(): View
    {
        return view('superadmin.notices.create');
    }

    public function store(StoreNoticeRequest $request): RedirectResponse
    {
        $validated = $request->validated();

        $validated['pin_to_dashboard'] = $request->boolean('pin_to_dashboard');
        $validated['send_email'] = $request->boolean('send_email');
        $validated['send_sms'] = $request->boolean('send_sms');
        $validated['require_acknowledgement'] = $request->boolean('require_acknowledgement');
        $validated['estimated_recipients'] = $validated['estimated_recipients'] ?? 0;
        $validated['status'] = $validated['status'] ?? 'published';
        $validated['created_by'] = auth()->id();

        Notice::create($validated);

        return redirect()->route('superadmin.notices.index')
            ->with('success', 'Notice created successfully.');
    }
}
