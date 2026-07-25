<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use App\Models\Role;
use App\Models\Society;
use App\Models\User;
use App\Notifications\SocietyAdminInvitation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Throwable;

class UserController extends Controller
{
    public function index()
    {
        $users = User::with('roles', 'society')->latest()->paginate(10);
        $totalUsers = User::count();
        $activeUsers = User::where('status', 'active')->count();
        $inactiveUsers = User::where('status', 'inactive')->count();
        $suspendedUsers = User::where('status', 'suspended')->count();

        return view('superadmin.user.index', compact('users', 'totalUsers', 'activeUsers', 'inactiveUsers', 'suspendedUsers'));
    }

    public function create()
    {
        $roles = Role::where('status', 'active')->get();
        $societies = Society::where('status', 'active')->get();

        return view('superadmin.user.create', compact('roles', 'societies'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'mobile' => 'required|string|max:20',
            'alternate_mobile' => 'nullable|string|max:20',
            'role_id' => ['required', Rule::exists('roles', 'id')->where('status', 'active')],
            'society_id' => ['nullable', Rule::exists('societies', 'id')->where('status', 'active')->whereNull('deleted_at')],
            'status' => 'required|in:active,inactive',
        ]);

        $role = Role::where('status', 'active')->findOrFail($validated['role_id']);
        $societyId = $role->name === 'super_admin' ? null : ($validated['society_id'] ?? null);
        if ($role->name !== 'super_admin' && ! $societyId) {
            throw ValidationException::withMessages(['society_id' => 'A society is required for this role.']);
        }

        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'mobile' => $validated['mobile'],
            'society_id' => $societyId,
            'password' => Str::random(64),
            'status' => $validated['status'],
        ]);

        $user->roles()->attach($role);

        try {
            if ($user->status !== 'active') {
                return redirect()->route('superadmin.users.index')->with('success', 'Inactive user created without sending an invitation.');
            }
            $token = Password::broker()->createToken($user);
            $user->notify(new SocietyAdminInvitation($token, $user->society?->name));
            $message = 'User created and a secure password setup invitation was sent.';
        } catch (Throwable $exception) {
            report($exception);
            $message = 'User created, but the invitation could not be sent. Use “Resend invitation” to try again.';
        }

        return redirect()->route('superadmin.users.index')->with('success', $message);
    }

    public function show(User $user)
    {
        $user->load('roles');

        return view('superadmin.user.show', compact('user'));
    }

    public function edit(User $user)
    {
        $roles = Role::where('status', 'active')->get();
        $societies = Society::where('status', 'active')->get();
        $user->load('roles', 'society');

        return view('superadmin.user.edit', compact('user', 'roles', 'societies'));
    }

    public function update(Request $request, User $user)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email,'.$user->id,
            'mobile' => 'required|string|max:20',
            'status' => 'required|in:active,inactive,suspended',
            'role_id' => ['required', Rule::exists('roles', 'id')->where('status', 'active')],
            'society_id' => ['nullable', Rule::exists('societies', 'id')->where('status', 'active')->whereNull('deleted_at')],
        ]);

        $role = Role::where('status', 'active')->findOrFail($validated['role_id']);
        $validated['society_id'] = $role->name === 'super_admin' ? null : ($validated['society_id'] ?? null);
        if ($role->name !== 'super_admin' && ! $validated['society_id']) {
            throw ValidationException::withMessages(['society_id' => 'A society is required for this role.']);
        }
        if ($user->is(auth()->user()) && ($role->name !== 'super_admin' || $validated['status'] !== 'active')) {
            throw ValidationException::withMessages(['role_id' => 'You cannot remove your own active super-admin access.']);
        }

        unset($validated['role_id']);

        $user->update($validated);
        $user->roles()->sync([$role->id]);

        return redirect()->route('superadmin.users.index')->with('success', 'User updated successfully');
    }

    public function destroy(User $user)
    {
        abort_if($user->is(auth()->user()), 422, 'You cannot delete your own account.');
        abort_if(
            $user->hasRole('super_admin') && User::whereHas('roles', fn ($query) => $query->where('name', 'super_admin'))->count() <= 1,
            422,
            'The final super-admin account cannot be deleted.'
        );

        $user->delete();

        return redirect()->route('superadmin.users.index')->with('success', 'User deleted successfully');
    }

    public function resendInvitation(User $user)
    {
        abort_unless($user->status === 'active', 422, 'Activate this user before sending an invitation.');

        $token = Password::broker()->createToken($user);
        $user->notify(new SocietyAdminInvitation($token, $user->society?->name));

        return back()->with('success', 'A fresh password setup invitation was sent to '.$user->email.'.');
    }

    public function loginActivity()
    {
        $activities = ActivityLog::where('action', 'like', '%login%')
            ->orWhere('description', 'like', '%login%')
            ->latest()
            ->paginate(20);

        return view('superadmin.user.login-activity', compact('activities'));
    }
}
