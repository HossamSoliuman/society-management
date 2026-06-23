<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\SystemLog;

class SystemLogSeeder extends Seeder
{
    public function run(): void
    {
        $logs = [
            ['level' => 'INFO', 'module' => 'User Management', 'user_name' => 'Super Admin', 'user_email' => 'superadmin@society.com', 'message' => 'User "Ankit Desai" updated successfully.', 'ip_address' => '192.168.1.10'],
            ['level' => 'SUCCESS', 'module' => 'Member', 'user_name' => 'Ankit Desai', 'user_email' => 'admin@greenfield.com', 'message' => 'New member "Rahul Sharma (Flat A-101)" added.', 'ip_address' => '192.168.1.15'],
            ['level' => 'WARNING', 'module' => 'Billing', 'user_name' => 'Meera Gupta', 'user_email' => 'meera@greenfield.com', 'message' => 'Invoice payment is overdue for Invoice #INV-2024-0456.', 'ip_address' => '192.168.1.22'],
            ['level' => 'INFO', 'module' => 'Maintenance', 'user_name' => 'Ravi Gupta', 'user_email' => 'ravi@greenfield.com', 'message' => 'Maintenance request status changed to "In Progress".', 'ip_address' => '192.168.1.18'],
            ['level' => 'ERROR', 'module' => 'System', 'user_name' => 'System', 'user_email' => null, 'message' => 'Failed to send email to user: noreply@greenfield.com', 'ip_address' => '192.168.1.10'],
            ['level' => 'LOGIN', 'module' => 'Authentication', 'user_name' => 'Neha Kapoor', 'user_email' => 'neha@greenfield.com', 'message' => 'User logged in successfully.', 'ip_address' => '192.168.1.33'],
            ['level' => 'SUCCESS', 'module' => 'Settings', 'user_name' => 'Building Staff', 'user_email' => 'staff@greenfield.com', 'message' => 'System settings updated.', 'ip_address' => '192.168.1.44'],
            ['level' => 'INFO', 'module' => 'Complaint', 'user_name' => 'Super Admin', 'user_email' => 'superadmin@society.com', 'message' => 'Complaint #C-2024-0156 status changed to "Resolved".', 'ip_address' => '192.168.1.10'],
            ['level' => 'WARNING', 'module' => 'Backup', 'user_name' => 'System', 'user_email' => null, 'message' => 'Database backup completed with warnings.', 'ip_address' => '192.168.1.22'],
            ['level' => 'ERROR', 'module' => 'Authentication', 'user_name' => 'Ankit Desai', 'user_email' => 'admin@greenfield.com', 'message' => 'Failed login attempt for user: admin@greenfield.com', 'ip_address' => '192.168.1.15'],
        ];

        foreach ($logs as $log) {
            SystemLog::create($log);
        }
    }
}
