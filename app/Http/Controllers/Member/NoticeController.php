<?php

namespace App\Http\Controllers\Member;

use App\Models\Notice;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * Platform notices visible to residents of this society (society-wide or
 * targeted at the "member" role).
 */
class NoticeController extends PortalController
{
    public function index(Request $request): View
    {
        $user = $request->user();

        $notices = Notice::query()
            ->with(['acknowledgements' => fn ($q) => $q->where('user_id', $user->id)])
            ->live()
            ->forUser($user)
            ->when($request->filled('q'), function ($q) use ($request) {
                $term = $request->string('q');
                $q->where(fn ($sub) => $sub->where('title', 'like', "%{$term}%")->orWhere('short_description', 'like', "%{$term}%"));
            })
            ->orderByDesc('pin_to_dashboard')
            ->orderByDesc('publish_at')
            ->paginate(10)
            ->withQueryString();

        return view('member.notices.index', [
            'member' => $this->currentMember(),
            'notices' => $notices,
        ]);
    }

    public function show(Request $request, Notice $notice): View
    {
        $user = $request->user();

        abort_unless(Notice::query()->live()->forUser($user)->whereKey($notice->id)->exists(), 404);

        return view('member.notices.show', [
            'member' => $this->currentMember(),
            'notice' => $notice,
            'acknowledged' => $notice->isAcknowledgedBy($user),
        ]);
    }

    public function acknowledge(Request $request, Notice $notice): RedirectResponse
    {
        $user = $request->user();

        abort_unless(Notice::query()->live()->forUser($user)->whereKey($notice->id)->exists(), 404);

        $notice->acknowledgements()->firstOrCreate(['user_id' => $user->id], ['acknowledged_at' => now()]);

        return back()->with('success', "Notice \"{$notice->title}\" acknowledged.");
    }
}
