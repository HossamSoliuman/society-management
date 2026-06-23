<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Announcement;

class AnnouncementSeeder extends Seeder
{
    public function run(): void
    {
        $announcements = [
            [
                'title' => 'Annual General Meeting 2025',
                'message' => 'The Annual General Meeting for the financial year 2024-2025 is scheduled for July 15, 2025. All members are requested to attend.',
                'recipient_type' => 'all_members',
                'estimated_recipients' => 320,
                'priority' => 'high',
                'category' => 'Meeting',
                'delivery_channel' => 'all',
                'send_type' => 'now',
                'status' => 'sent',
                'sent_at' => now()->subDays(5),
                'created_by' => 1,
            ],
            [
                'title' => 'Water Supply Maintenance',
                'message' => 'Water supply will be interrupted on Sunday from 10 AM to 2 PM for maintenance work. Please store water accordingly.',
                'recipient_type' => 'all_residents',
                'estimated_recipients' => 280,
                'priority' => 'normal',
                'category' => 'Maintenance',
                'delivery_channel' => 'in_app',
                'send_type' => 'scheduled',
                'scheduled_at' => now()->addDays(2),
                'status' => 'scheduled',
                'created_by' => 1,
            ],
        ];

        foreach ($announcements as $announcement) {
            Announcement::create($announcement);
        }
    }
}
