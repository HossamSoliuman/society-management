<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Role;
use App\Models\Society;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{
    public function index()
    {
        $users = User::with('roles')->latest()->paginate(10);
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
        $plans = \App\Models\SubscriptionPlan::where('status', 'active')->get();
        return view('superadmin.user.create', compact('roles', 'societies', 'plans'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'mobile' => 'required|string|max:20',
            'alternate_mobile' => 'nullable|string|max:20',
            'password' => 'required|string|min:8',
            'confirm_password' => 'required|same:password',
            'role_id' => 'required|exists:roles,id',
            'designation' => 'nullable|string|max:255',
            'status' => 'required|in:active,inactive',
        ]);

        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'mobile' => $validated['mobile'],
            'password' => Hash::make($validated['password']),
            'status' => $validated['status'],
        ]);

        $user->roles()->attach($validated['role_id']);

        return redirect()->route('superadmin.users.index')->with('success', 'User created successfully');
    }

    public function show(User $user)
    {
        $user->load('roles');
        return view('superadmin.user.show', compact('user'));
    }

    public function edit(User $user)
    {
        $roles = Role::where('status', 'active')->get();
        $user->load('roles');
        return view('superadmin.user.edit', compact('user', 'roles'));
    }

    public function update(Request $request, User $user)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email,' . $user->id,
            'mobile' => 'required|string|max:20',
            'status' => 'required|in:active,inactive,suspended',
        ]);

        $user->update($validated);

        return redirect()->route('superadmin.users.index')->with('success', 'User updated successfully');
    }

    public function destroy(User $user)
    {
        $user->delete();
        return redirect()->route('superadmin.users.index')->with('success', 'User deleted successfully');
    }
}
