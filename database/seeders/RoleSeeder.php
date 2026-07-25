<?php

namespace Database\Seeders;

use App\Models\Permission;
use App\Models\Role;
use Illuminate\Database\Seeder;

class RoleSeeder extends Seeder
{
    public function run(): void
    {
        $roles = [
            ['name' => 'super_admin', 'display_name' => 'Super Admin', 'description' => 'Full platform access'],
            ['name' => 'society_admin', 'display_name' => 'Society Admin', 'description' => 'Society-level management'],
            ['name' => 'manager', 'display_name' => 'Manager', 'description' => 'Limited management access'],
            ['name' => 'staff', 'display_name' => 'Staff', 'description' => 'Basic operational access'],
            ['name' => 'accountant', 'display_name' => 'Accountant', 'description' => 'Billing and finance access'],
        ];

        foreach ($roles as $role) {
            Role::updateOrCreate(['name' => $role['name']], $role);
        }

        $permissions = [
            ['name' => 'dashboard.view', 'display_name' => 'View Dashboard', 'module' => 'dashboard'],
            ['name' => 'societies.manage', 'display_name' => 'Manage Societies', 'module' => 'society'],
            ['name' => 'subscriptions.manage', 'display_name' => 'Manage Subscriptions', 'module' => 'subscription'],
            ['name' => 'billing.manage', 'display_name' => 'Manage Billing', 'module' => 'billing'],
            ['name' => 'users.manage', 'display_name' => 'Manage Users', 'module' => 'user'],
            ['name' => 'reports.view', 'display_name' => 'View Reports', 'module' => 'reports'],
            ['name' => 'notifications.send', 'display_name' => 'Send Notifications', 'module' => 'notification'],
            ['name' => 'tickets.manage', 'display_name' => 'Manage Tickets', 'module' => 'ticket'],
            ['name' => 'logs.view', 'display_name' => 'View Logs', 'module' => 'logs'],
            ['name' => 'masters.manage', 'display_name' => 'Manage Masters', 'module' => 'master'],
            ['name' => 'terms.manage', 'display_name' => 'Manage Terms', 'module' => 'terms'],
            ['name' => 'settings.manage', 'display_name' => 'Manage Settings', 'module' => 'settings'],
        ];

        foreach ($permissions as $permission) {
            Permission::updateOrCreate(['name' => $permission['name']], $permission);
        }

        $permissionIds = Permission::pluck('id', 'name');
        Role::where('name', 'super_admin')->firstOrFail()->permissions()->sync($permissionIds->values());
        Role::where('name', 'society_admin')->firstOrFail()->permissions()->sync($permissionIds->only([
            'dashboard.view', 'billing.manage', 'reports.view', 'tickets.manage',
        ])->values());
        Role::where('name', 'manager')->firstOrFail()->permissions()->sync($permissionIds->only([
            'dashboard.view', 'reports.view', 'tickets.manage',
        ])->values());
        Role::where('name', 'accountant')->firstOrFail()->permissions()->sync($permissionIds->only([
            'dashboard.view', 'billing.manage', 'reports.view',
        ])->values());
        Role::where('name', 'staff')->firstOrFail()->permissions()->sync($permissionIds->only([
            'dashboard.view', 'tickets.manage',
        ])->values());
    }
}
