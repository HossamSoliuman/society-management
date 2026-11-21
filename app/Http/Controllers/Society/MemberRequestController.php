<?php

namespace App\Http\Controllers\Society;

use App\Http\Controllers\Controller;
use App\Models\MemberRequest;
use App\Models\Unit;
use App\Services\MemberRequestService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class MemberRequestController extends Controller
{
    public function __construct(private readonly MemberRequestService $requests) {}

    public function index(Request $request): View
    {
        $society = $this->currentSociety();
        $status = array_key_exists((string) $request->query('status'), MemberRequest::STATUSES) ? (string) $request->query('status') : 'pending';

        $items = MemberRequest::query()
            ->forSociety($society)
            ->with(['unit', 'reviewer'])
            ->where('status', $status)
            ->when($request->filled('q'), function ($q) use ($request) {
                $term = $request->string('q');
                $q->where(fn ($sub) => $sub->where('name', 'like', "%{$term}%")
                    ->orWhere('mobile', 'like', "%{$term}%")
                    ->orWhere('unit_number', 'like', "%{$term}%"));
            })
            ->latest('id')
            ->paginate(15)
            ->withQueryString();

        $counts = MemberRequest::query()->forSociety($society)->selectRaw('status, COUNT(*) as c')->groupBy('status')->pluck('c', 'status');

        return view('society.member-requests.index', [
            'items' => $items,
            'status' => $status,
            'counts' => $counts,
            'joinUrl' => route('join.show', $society->joinCode()),
        ]);
    }

    public function show(MemberRequest $memberRequest): View
    {
        $society = $this->currentSociety();

        return view('society.member-requests.show', [
            'item' => $memberRequest->load(['unit', 'member', 'reviewer']),
            'units' => Unit::query()->forSociety($society)->orderBy('wing')->orderBy('unit_number')->get(['id', 'unit_number', 'status', 'owner_name']),
            'matchedUnit' => $memberRequest->unit ?? Unit::query()->forSociety($society)->where('unit_number', $memberRequest->unit_number)->first(),
        ]);
    }

    public function approve(Request $request, MemberRequest $memberRequest): RedirectResponse
    {
        $society = $this->currentSociety();
        $data = $request->validate([
            'unit_id' => ['required', Rule::exists('units', 'id')->where('society_id', $society->id)->whereNull('deleted_at')],
            'review_note' => ['nullable', 'string', 'max:255'],
        ], [], ['unit_id' => 'unit']);

        $member = $this->requests->approve($memberRequest, $request->user(), Unit::find($data['unit_id']), $data['review_note'] ?? null);

        return redirect()->route('society.members.show', $member)
            ->with('success', "{$member->name} added as ".strtolower($memberRequest->memberTypeLabel())." of {$member->flat_unit}.".($member->email ? ' You can invite them to the portal below.' : ''));
    }

    public function reject(Request $request, MemberRequest $memberRequest): RedirectResponse
    {
        $data = $request->validate(['review_note' => ['nullable', 'string', 'max:255']]);
        $this->requests->reject($memberRequest, $request->user(), $data['review_note'] ?? null);

        return redirect()->route('society.member-requests.index')->with('success', "Request from {$memberRequest->name} rejected.");
    }

    public function document(MemberRequest $memberRequest): StreamedResponse
    {
        abort_unless($memberRequest->document_path && Storage::disk('local')->exists($memberRequest->document_path), 404);

        return Storage::disk('local')->download($memberRequest->document_path, 'request-'.$memberRequest->id.'-document.'.pathinfo($memberRequest->document_path, PATHINFO_EXTENSION));
    }
}
