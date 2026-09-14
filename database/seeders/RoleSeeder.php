<?php

namespace Database\Seeders;

use App\Models\Permission;
use App\Models\Role;
use Illuminate\Database\Seeder;

class RoleSeeder extends Seeder
{
    /**
     * Society-panel permissions granted to each society role. `society_admin`
     * always receives every society permission (see run()).
     *
     * @var array<string, array<int, string>>
     */
    public const SOCIETY_ROLE_PERMISSIONS = [
        'manager' => [
            'dashboard.view', 'members.manage', 'billing.manage', 'expenses.manage', 'assets.manage',
            'support.manage', 'vendors.manage', 'documents.manage', 'notices.view', 'reports.view',
        ],
        'accountant' => [
            'dashboard.view', 'billing.manage', 'accounting.manage', 'expenses.manage', 'notices.view', 'reports.view',
        ],
        'staff' => [
            'dashboard.view', 'members.manage', 'support.manage', 'documents.manage', 'notices.view',
        ],
    ];

    /**
     * Every permission that belongs to the society panel.
     *
     * @var array<int, string>
     */
    public const SOCIETY_PERMISSIONS = [
        'dashboard.view', 'members.manage', 'billing.manage', 'expenses.manage', 'assets.manage',
        'support.manage', 'accounting.manage', 'vendors.manage', 'documents.manage', 'notices.view',
        'team.manage', 'reports.view', 'settings.manage',
    ];

    public function run(): void
    {
        $roles = [
            ['name' => 'super_admin', 'display_name' => 'Super Admin', 'description' => 'Full platform access'],
            ['name' => 'society_admin', 'display_name' => 'Society Admin', 'description' => 'Society-level management'],
            ['name' => 'manager', 'display_name' => 'Manager', 'description' => 'Limited management access'],
            ['name' => 'staff', 'display_name' => 'Staff', 'description' => 'Basic operational access'],
            ['name' => 'accountant', 'display_name' => 'Accountant', 'description' => 'Billing and finance access'],
            ['name' => 'member', 'display_name' => 'Member', 'description' => 'Resident portal access'],
        ];

        foreach ($roles as $role) {
            Role::updateOrCreate(['name' => $role['name']], $role);
        }

        $permissions = [
            // Platform (super admin)
            ['name' => 'dashboard.view', 'display_name' => 'View Dashboard', 'module' => 'dashboard'],
            ['name' => 'societies.manage', 'display_name' => 'Manage Societies', 'module' => 'society'],
            ['name' => 'subscriptions.manage', 'display_name' => 'Manage Subscriptions', 'module' => 'subscription'],
            ['name' => 'users.manage', 'display_name' => 'Manage Users', 'module' => 'user'],
            ['name' => 'notifications.send', 'display_name' => 'Send Notifications', 'module' => 'notification'],
            ['name' => 'tickets.manage', 'display_name' => 'Manage Tickets', 'module' => 'ticket'],
            ['name' => 'logs.view', 'display_name' => 'View Logs', 'module' => 'logs'],
            ['name' => 'masters.manage', 'display_name' => 'Manage Masters', 'module' => 'master'],
            ['name' => 'terms.manage', 'display_name' => 'Manage Terms', 'module' => 'terms'],
            ['name' => 'settings.manage', 'display_name' => 'Manage Settings', 'module' => 'settings'],
            ['name' => 'reports.view', 'display_name' => 'View Reports', 'module' => 'reports'],
            // Society panel modules
            ['name' => 'members.manage', 'display_name' => 'Manage Members & Units', 'module' => 'members'],
            ['name' => 'billing.manage', 'display_name' => 'Manage Billing & Collections', 'module' => 'billing'],
            ['name' => 'expenses.manage', 'display_name' => 'Manage Expenses', 'module' => 'expenses'],
            ['name' => 'assets.manage', 'display_name' => 'Manage Assets', 'module' => 'assets'],
            ['name' => 'support.manage', 'display_name' => 'Manage Support & Complaints', 'module' => 'support'],
            ['name' => 'accounting.manage', 'display_name' => 'Manage Accounting', 'module' => 'accounting'],
            ['name' => 'vendors.manage', 'display_name' => 'Manage Vendors, AMC & Tenders', 'module' => 'vendors'],
            ['name' => 'documents.manage', 'display_name' => 'Manage Documents', 'module' => 'documents'],
            ['name' => 'notices.view', 'display_name' => 'View Notices', 'module' => 'notices'],
            ['name' => 'team.manage', 'display_name' => 'Manage Society Users', 'module' => 'team'],
        ];

        foreach ($permissions as $permission) {
            Permission::updateOrCreate(['name' => $permission['name']], $permission);
        }

        $permissionIds = Permission::pluck('id', 'name');

        Role::where('name', 'super_admin')->firstOrFail()->permissions()->sync($permissionIds->values());
        Role::where('name', 'society_admin')->firstOrFail()->permissions()->sync(
            $permissionIds->only(self::SOCIETY_PERMISSIONS)->values()
        );

        foreach (self::SOCIETY_ROLE_PERMISSIONS as $roleName => $names) {
            Role::where('name', $roleName)->firstOrFail()->permissions()->sync(
                $permissionIds->only($names)->values()
            );
        }

        Role::where('name', 'member')->firstOrFail()->permissions()->sync([]);
    }
}
