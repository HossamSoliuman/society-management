<?php

namespace App\Http\Controllers\Member;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class ProfileController extends PortalController
{
    public function show(Request $request): View
    {
        $member = $this->currentMember()->load(['society', 'units']);

        return view('member.profile', [
            'member' => $member,
            'society' => $member->society,
            'user' => $request->user(),
        ]);
    }

    /**
     * Residents may correct their own contact details; unit/tower assignment
     * and status remain office-controlled.
     */
    public function update(Request $request): RedirectResponse
    {
        $member = $this->currentMember();
        $user = $request->user();

        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'mobile' => ['nullable', 'string', 'max:20'],
            'email' => ['required', 'email', 'max:255', Rule::unique('users', 'email')->ignore($user->id)],
        ]);

        DB::transaction(function () use ($member, $user, $data): void {
            $member->update($data);
            $user->update($data);
        });

        return redirect()->route('member.profile')->with('success', 'Profile updated.');
    }

    public function updatePassword(Request $request): RedirectResponse
    {
        $request->validate([
            'current_password' => ['required', 'string'],
            'new_password' => ['required', 'string', 'min:8', 'confirmed'],
        ]);

        $user = $request->user();

        if (! Hash::check($request->current_password, $user->password)) {
            return back()->withErrors(['current_password' => 'The current password is incorrect.']);
        }

        $user->update(['password' => Hash::make($request->new_password)]);

        return redirect()->route('member.profile')->with('success', 'Password changed successfully.');
    }
}
