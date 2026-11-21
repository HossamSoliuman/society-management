<?php

namespace App\Http\Controllers\Society;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreSocietyNoticeRequest;
use App\Jobs\DeliverAnnouncement;
use App\Models\Notice;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

/**
 * Notices a society office writes for its own residents and team
 * (the read-only board lives in NoticeController).
 */
class NoticeManagementController extends Controller
{
    public function index(Request $request): View
    {
        $society = $this->currentSociety();

        $notices = Notice::query()
            ->authoredBySociety($society)
            ->with('creator')
            ->withCount('acknowledgements')
            ->when($request->filled('status'), fn ($q) => $q->where('status', $request->string('status')))
            ->when($request->filled('q'), fn ($q) => $q->where('title', 'like', '%'.$request->string('q').'%'))
            ->latest('id')
            ->paginate(10)
            ->withQueryString();

        $base = Notice::query()->authoredBySociety($society);

        return view('society.notices.manage', [
            'notices' => $notices,
            'audiences' => Notice::SOCIETY_AUDIENCES,
            'stats' => [
                'total' => (clone $base)->count(),
                'live' => (clone $base)->live()->count(),
                'scheduled' => (clone $base)->where('status', 'scheduled')->count(),
                'draft' => (clone $base)->where('status', 'draft')->count(),
            ],
        ]);
    }

    public function create(): View
    {
        return view('society.notices.create', [
            'notice' => new Notice(['notice_type' => 'general', 'priority' => 'medium', 'publish_at' => now()]),
            'audiences' => Notice::SOCIETY_AUDIENCES,
        ]);
    }

    public function store(StoreSocietyNoticeRequest $request): RedirectResponse
    {
        $society = $this->currentSociety();

        $notice = new Notice([
            'society_id' => $society->id,
            'origin' => 'society',
            'audience_type' => 'all_members',
            'created_by' => $request->user()->id,
        ]);

        $this->fill($notice, $request);
        $notice->save();

        $this->deliverIfLive($notice);

        return redirect()->route('society.notices.manage')->with('success', $this->savedMessage($notice));
    }

    public function edit(Notice $notice): View
    {
        $this->authorizeOwn($notice);

        return view('society.notices.edit', [
            'notice' => $notice,
            'audiences' => Notice::SOCIETY_AUDIENCES,
        ]);
    }

    public function update(StoreSocietyNoticeRequest $request, Notice $notice): RedirectResponse
    {
        $this->authorizeOwn($notice);

        $wasPublished = $notice->status === 'published';
        $this->fill($notice, $request);
        $notice->save();

        // Already-delivered notices are corrected in place without re-sending.
        if (! $wasPublished) {
            $this->deliverIfLive($notice);
        }

        return redirect()->route('society.notices.manage')->with('success', $this->savedMessage($notice));
    }

    public function destroy(Notice $notice): RedirectResponse
    {
        $this->authorizeOwn($notice);

        if ($notice->attach_path) {
            Storage::disk('public')->delete($notice->attach_path);
        }
        $notice->delete();

        return redirect()->route('society.notices.manage')->with('success', 'Notice deleted.');
    }

    private function fill(Notice $notice, StoreSocietyNoticeRequest $request): void
    {
        $data = $request->validated();
        $publishAt = ! empty($data['publish_at']) ? Carbon::parse($data['publish_at']) : now();

        if ($request->boolean('remove_attachment') && $notice->attach_path) {
            Storage::disk('public')->delete($notice->attach_path);
            $notice->attach_path = null;
        }
        if ($request->hasFile('attachment')) {
            if ($notice->attach_path) {
                Storage::disk('public')->delete($notice->attach_path);
            }
            $notice->attach_path = $request->file('attachment')->store('notice-attachments', 'public');
        }

        $notice->fill([
            'title' => $data['title'],
            'notice_type' => $data['notice_type'],
            'priority' => $data['priority'],
            'short_description' => $data['short_description'] ?? null,
            'content' => $data['content'],
            'target_roles' => Notice::SOCIETY_AUDIENCES[$data['audience']]['roles'],
            'publish_at' => $publishAt,
            'expires_at' => $data['expires_at'] ?? null,
            'pin_to_dashboard' => $request->boolean('pin_to_dashboard'),
            'send_email' => $request->boolean('send_email'),
            'send_sms' => $request->boolean('send_sms'),
            'require_acknowledgement' => $request->boolean('require_acknowledgement'),
        ]);

        if ($data['action'] === 'draft') {
            $notice->status = 'draft';
        } elseif ($notice->status !== 'published') {
            $notice->status = $publishAt->isFuture() ? 'scheduled' : 'published';
        }

        $notice->estimated_recipients = $notice->countRecipients();
    }

    private function deliverIfLive(Notice $notice): void
    {
        if ($notice->status === 'published') {
            DeliverAnnouncement::dispatch($notice);
        }
    }

    private function savedMessage(Notice $notice): string
    {
        return match ($notice->status) {
            'published' => "Notice published to {$notice->estimated_recipients} recipient(s).",
            'scheduled' => "Notice scheduled for {$notice->publish_at->format('d M Y, h:i A')}.",
            default => 'Notice saved as draft.',
        };
    }

    /**
     * Only this society's own notices can be changed from the society panel.
     */
    private function authorizeOwn(Notice $notice): void
    {
        $society = $this->currentSociety();

        abort_unless($notice->isSocietyAuthored() && (int) $notice->society_id === $society->id, 404);
    }
}
