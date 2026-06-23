<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Role;
use App\Models\Permission;

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
            Role::create($role);
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
            Permission::create($permission);
        }
    }
}
