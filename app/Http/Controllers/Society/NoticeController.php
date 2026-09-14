<?php

namespace App\Http\Controllers\Society;

use App\Http\Controllers\Controller;
use App\Models\Notice;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * Notices published by the platform that target this society (or all
 * societies) and the signed-in user's role.
 */
class NoticeController extends Controller
{
    public function index(Request $request): View
    {
        $society = $this->currentSociety();
        $user = $request->user();

        $notices = Notice::query()
            ->with(['society', 'acknowledgements' => fn ($q) => $q->where('user_id', $user->id)])
            ->live()
            ->forUser($user)
            ->when($request->filled('type'), fn ($q) => $q->where('notice_type', $request->string('type')))
            ->when($request->filled('priority'), fn ($q) => $q->where('priority', $request->string('priority')))
            ->when($request->filled('q'), function ($q) use ($request) {
                $term = $request->string('q');
                $q->where(fn ($sub) => $sub->where('title', 'like', "%{$term}%")->orWhere('short_description', 'like', "%{$term}%"));
            })
            ->orderByDesc('pin_to_dashboard')
            ->orderByDesc('publish_at')
            ->paginate(10)
            ->withQueryString();

        $pendingAck = Notice::query()
            ->live()
            ->forUser($user)
            ->where('require_acknowledgement', true)
            ->whereDoesntHave('acknowledgements', fn ($q) => $q->where('user_id', $user->id))
            ->count();

        return view('society.notices.index', [
            'society' => $society,
            'notices' => $notices,
            'pendingAck' => $pendingAck,
        ]);
    }

    public function show(Request $request, Notice $notice): View
    {
        $user = $request->user();

        abort_unless(Notice::query()->live()->forUser($user)->whereKey($notice->id)->exists(), 404);

        return view('society.notices.show', [
            'notice' => $notice->load('society'),
            'acknowledged' => $notice->isAcknowledgedBy($user),
        ]);
    }

    public function acknowledge(Request $request, Notice $notice): RedirectResponse
    {
        $user = $request->user();

        abort_unless(Notice::query()->live()->forUser($user)->whereKey($notice->id)->exists(), 404);

        $notice->acknowledgements()->firstOrCreate(
            ['user_id' => $user->id],
            ['acknowledged_at' => now()]
        );

        return back()->with('success', "Notice \"{$notice->title}\" acknowledged.");
    }
}
