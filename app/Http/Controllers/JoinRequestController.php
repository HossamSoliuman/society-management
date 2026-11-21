<?php

namespace App\Http\Controllers;

use App\Models\MemberRequest;
use App\Models\Society;
use App\Models\User;
use App\Notifications\MemberRequestReceived;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

/**
 * Public "request to join" form reached through a society's share link.
 */
class JoinRequestController extends Controller
{
    public function show(string $code): View
    {
        return view('join.create', ['society' => $this->society($code), 'code' => $code]);
    }

    public function store(Request $request, string $code): RedirectResponse
    {
        $society = $this->society($code);

        $data = $request->validate([
            'request_type' => ['required', Rule::in(array_keys(MemberRequest::TYPES))],
            'member_type' => ['required', Rule::in(array_keys(MemberRequest::MEMBER_TYPES))],
            'name' => ['required', 'string', 'max:255'],
            'mobile' => ['required', 'string', 'max:30'],
            'email' => ['nullable', 'email', 'max:255'],
            'unit_number' => ['required', 'string', 'max:50'],
            'move_in_date' => ['nullable', 'date'],
            'message' => ['nullable', 'string', 'max:1000'],
            'document' => ['nullable', 'file', 'mimes:pdf,jpg,jpeg,png', 'max:5120'],
        ], [], ['unit_number' => 'flat / unit number', 'member_type' => 'you are joining as']);

        $memberRequest = MemberRequest::create([
            'society_id' => $society->id,
            'request_type' => $data['request_type'],
            'member_type' => $data['request_type'] === 'transfer' ? 'owner' : $data['member_type'],
            'name' => $data['name'],
            'mobile' => $data['mobile'],
            'email' => $data['email'] ?? null,
            'unit_number' => strtoupper(trim($data['unit_number'])),
            'unit_id' => $society->units()->where('unit_number', strtoupper(trim($data['unit_number'])))->value('id'),
            'move_in_date' => $data['move_in_date'] ?? null,
            'message' => $data['message'] ?? null,
            'document_path' => $request->hasFile('document') ? $request->file('document')->store("member-requests/{$society->id}", 'local') : null,
            'status' => 'pending',
            'ip_address' => $request->ip(),
        ]);

        User::query()
            ->where('society_id', $society->id)
            ->where('status', 'active')
            ->whereHas('roles', fn ($q) => $q->whereIn('name', ['society_admin', 'manager']))
            ->get()
            ->each(fn (User $user) => $user->notify(new MemberRequestReceived($memberRequest)));

        return redirect()->route('join.show', $code)->with('status', 'Thank you. The society office will review your request and contact you.');
    }

    private function society(string $code): Society
    {
        return Society::query()
            ->where('join_code', $code)
            ->where('status', 'active')
            ->firstOr(fn () => abort(404));
    }
}
