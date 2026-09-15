<?php

namespace App\Http\Controllers\Society;

use App\Http\Controllers\Controller;
use App\Models\Member;
use App\Models\Role;
use App\Models\User;
use App\Notifications\MemberPortalInvitation;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Throwable;

class MemberController extends Controller
{
    public function index(Request $request)
    {
        $society = $this->currentSociety();

        $query = $society->members()->latest('join_date');

        if ($search = $request->input('q')) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('mobile', 'like', "%{$search}%")
                    ->orWhere('flat_unit', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%");
            });
        }

        if ($tower = $request->input('tower')) {
            $query->where('tower_wing', $tower);
        }

        if ($unit = $request->input('unit')) {
            $query->where('flat_unit', $unit);
        }

        if ($status = $request->input('status')) {
            $query->where('status', $status);
        }

        if ($type = $request->input('type')) {
            $query->where('member_type', $type);
        }

        $members = $query->paginate(8)->withQueryString();

        $stats = [
            'total' => $society->members()->count(),
            'active' => $society->members()->where('status', 'active')->count(),
            'inactive' => $society->members()->where('status', 'inactive')->count(),
            'blocked' => $society->members()->where('status', 'blocked')->count(),
        ];

        $towers = $society->members()->whereNotNull('tower_wing')->distinct()->orderBy('tower_wing')->pluck('tower_wing');
        $units = $society->members()->whereNotNull('flat_unit')->distinct()->orderBy('flat_unit')->pluck('flat_unit');

        return view('society.members.index', compact('society', 'members', 'stats', 'towers', 'units'));
    }

    public function create()
    {
        $society = $this->currentSociety();

        return view('society.members.create', compact('society'));
    }

    public function store(Request $request)
    {
        $society = $this->currentSociety();

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'member_type' => 'required|in:owner,family_member,tenant',
            'flat_unit' => 'nullable|string|max:50',
            'tower_wing' => 'nullable|string|max:50',
            'mobile' => 'nullable|string|max:20',
            'email' => 'nullable|email|max:255',
            'status' => 'required|in:active,inactive,blocked',
            'join_date' => 'nullable|date',
        ]);

        $society->members()->create($validated);

        return redirect()->route('society.members.index')->with('success', 'Member added successfully.');
    }

    public function show(Member $member)
    {
        $member->load(['user', 'familyMembers', 'vehicles']);

        return view('society.members.show', compact('member'));
    }

    /**
     * "Invite to portal": create (or reuse) a member-role login for this
     * member and email a password-setup link.
     */
    public function invite(Member $member): RedirectResponse
    {
        $society = $this->currentSociety();

        if (! $member->email) {
            throw ValidationException::withMessages(['email' => 'Add an email address to this member before inviting them.']);
        }

        $user = DB::transaction(function () use ($member, $society): User {
            $user = $member->user;

            if (! $user) {
                $existing = User::where('email', $member->email)->first();
                if ($existing && ($existing->society_id !== $society->id || ! $existing->hasRole('member') || $existing->member()->exists())) {
                    throw ValidationException::withMessages(['email' => 'This email already belongs to another account.']);
                }

                $user = $existing ?? User::create([
                    'society_id' => $society->id,
                    'name' => $member->name,
                    'email' => $member->email,
                    'mobile' => $member->mobile,
                    'password' => Str::random(64),
                    'status' => 'active',
                ]);

                $memberRole = Role::where('name', 'member')->firstOrFail();
                $user->roles()->syncWithoutDetaching([$memberRole->id]);
            }

            $user->update(['status' => 'active']);
            $member->forceFill(['user_id' => $user->id, 'invited_at' => now()])->save();

            return $user;
        });

        try {
            $token = Password::broker()->createToken($user);
            $user->notify(new MemberPortalInvitation($token, $society->name));
        } catch (Throwable $exception) {
            report($exception);

            return back()->with('error', 'The portal account was created but the invitation email could not be sent. Try again.');
        }

        return back()->with('success', "Portal invitation sent to {$user->email}.");
    }

    public function edit(Member $member)
    {
        return view('society.members.edit', compact('member'));
    }

    public function update(Request $request, Member $member)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'member_type' => 'required|in:owner,family_member,tenant',
            'flat_unit' => 'nullable|string|max:50',
            'tower_wing' => 'nullable|string|max:50',
            'mobile' => 'nullable|string|max:20',
            'email' => 'nullable|email|max:255',
            'status' => 'required|in:active,inactive,blocked',
            'join_date' => 'nullable|date',
        ]);

        $member->update($validated);

        return redirect()->route('society.members.index')->with('success', 'Member updated successfully.');
    }

    public function destroy(Member $member)
    {
        $member->delete();

        return redirect()->route('society.members.index')->with('success', 'Member deleted successfully.');
    }
}
