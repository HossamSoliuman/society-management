<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Models\Permission;
use App\Models\Role;
use Illuminate\Http\Request;

class RoleController extends Controller
{
    public function index()
    {
        $roles = Role::withCount('permissions')->latest()->paginate(15);
        $totalRoles = Role::count();
        $activeRoles = Role::where('status', 'active')->count();

        return view('superadmin.role.index', compact('roles', 'totalRoles', 'activeRoles'));
    }

    public function create()
    {
        $permissions = Permission::orderBy('module')->orderBy('display_name')->get()->groupBy('module');

        return view('superadmin.role.create', compact('permissions'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:roles,name',
            'display_name' => 'required|string|max:255',
            'description' => 'nullable|string|max:1000',
            'status' => 'required|in:active,inactive',
            'permissions' => 'nullable|array',
            'permissions.*' => 'exists:permissions,id',
        ]);

        $role = Role::create([
            'name' => $validated['name'],
            'display_name' => $validated['display_name'],
            'description' => $validated['description'] ?? null,
            'status' => $validated['status'],
            'guard_name' => 'web',
        ]);

        if (! empty($validated['permissions'])) {
            $role->permissions()->sync($validated['permissions']);
        }

        return redirect()->route('superadmin.roles.index')->with('success', 'Role created successfully.');
    }

    public function edit(Role $role)
    {
        $role->load('permissions');
        $permissions = Permission::orderBy('module')->orderBy('display_name')->get()->groupBy('module');
        $assignedPermissions = $role->permissions->pluck('id')->toArray();

        return view('superadmin.role.edit', compact('role', 'permissions', 'assignedPermissions'));
    }

    public function update(Request $request, Role $role)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:roles,name,'.$role->id,
            'display_name' => 'required|string|max:255',
            'description' => 'nullable|string|max:1000',
            'status' => 'required|in:active,inactive',
            'permissions' => 'nullable|array',
            'permissions.*' => 'exists:permissions,id',
        ]);

        $role->update([
            'name' => $validated['name'],
            'display_name' => $validated['display_name'],
            'description' => $validated['description'] ?? null,
            'status' => $validated['status'],
        ]);

        $role->permissions()->sync($validated['permissions'] ?? []);

        return redirect()->route('superadmin.roles.index')->with('success', 'Role updated successfully.');
    }

    public function destroy(Role $role)
    {
        $role->permissions()->detach();
        $role->delete();

        return redirect()->route('superadmin.roles.index')->with('success', 'Role deleted successfully.');
    }
}
