<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\ActivityLog;

class ActivityLogSeeder extends Seeder
{
    public function run(): void
    {
        $logs = [
            ['user_name' => 'Super Admin', 'user_email' => 'superadmin@society.com', 'action' => 'Update', 'module' => 'Society', 'description' => 'Updated society profile for Greenfield Residency', 'ip_address' => '192.168.1.10', 'status' => 'success'],
            ['user_name' => 'Ankit Desai', 'user_email' => 'admin@greenfield.com', 'action' => 'Create', 'module' => 'Member', 'description' => 'Added new member Rahul Sharma (Flat A-101)', 'ip_address' => '192.168.1.15', 'status' => 'success'],
            ['user_name' => 'Meera Gupta', 'user_email' => 'meera@greenfield.com', 'action' => 'Delete', 'module' => 'Announcement', 'description' => 'Deleted announcement "Maintenance Schedule"', 'ip_address' => '192.168.1.22', 'status' => 'success'],
            ['user_name' => 'Ravi Gupta', 'user_email' => 'ravi@greenfield.com', 'action' => 'Update', 'module' => 'Billing', 'description' => 'Updated maintenance amount for Tower A', 'ip_address' => '192.168.1.18', 'status' => 'success'],
            ['user_name' => 'Super Admin', 'user_email' => 'superadmin@society.com', 'action' => 'Login', 'module' => 'System', 'description' => 'User logged in to the system', 'ip_address' => '192.168.1.10', 'status' => 'success'],
            ['user_name' => 'Neha Kapoor', 'user_email' => 'neha@greenfield.com', 'action' => 'Update', 'module' => 'Visitor', 'description' => 'Updated visitor entry for Arjun Mehta', 'ip_address' => '192.168.1.33', 'status' => 'success'],
            ['user_name' => 'Building Staff', 'user_email' => 'staff@greenfield.com', 'action' => 'Create', 'module' => 'Complaint', 'description' => 'Created complaint #C-2024-0156 for water leakage', 'ip_address' => '192.168.1.44', 'status' => 'success'],
            ['user_name' => 'Super Admin', 'user_email' => 'superadmin@society.com', 'action' => 'Update', 'module' => 'User', 'description' => 'Updated role for user Ankit Desai', 'ip_address' => '192.168.1.10', 'status' => 'success'],
            ['user_name' => 'Ankit Desai', 'user_email' => 'admin@greenfield.com', 'action' => 'Login Failed', 'module' => 'System', 'description' => 'Failed login attempt', 'ip_address' => '192.168.1.15', 'status' => 'failed'],
            ['user_name' => 'Meera Gupta', 'user_email' => 'meera@greenfield.com', 'action' => 'Download', 'module' => 'Reports', 'description' => 'Downloaded member list report', 'ip_address' => '192.168.1.22', 'status' => 'success'],
        ];

        foreach ($logs as $log) {
            ActivityLog::create($log);
        }
    }
}
