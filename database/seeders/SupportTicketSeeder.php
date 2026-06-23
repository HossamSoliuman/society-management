<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\SupportTicket;

class SupportTicketSeeder extends Seeder
{
    public function run(): void
    {
        $tickets = [
            [
                'ticket_number' => 'TKT-2025-0001',
                'subject' => 'Login issue with mobile app',
                'description' => 'Unable to login to the mobile app since yesterday. Getting error code 403.',
                'category' => 'Technical',
                'priority' => 'high',
                'status' => 'in_progress',
                'created_by' => 2,
                'assigned_to' => 1,
                'society_id' => 1,
            ],
            [
                'ticket_number' => 'TKT-2025-0002',
                'subject' => 'Incorrect maintenance bill amount',
                'description' => 'The maintenance bill for June shows incorrect amount for Flat B-204.',
                'category' => 'Billing',
                'priority' => 'medium',
                'status' => 'open',
                'created_by' => 3,
                'assigned_to' => 1,
                'society_id' => 2,
            ],
            [
                'ticket_number' => 'TKT-2025-0003',
                'subject' => 'Request for visitor pass feature',
                'description' => 'Would like to request a pre-approved visitor pass feature for frequent visitors.',
                'category' => 'Feature Request',
                'priority' => 'low',
                'status' => 'open',
                'created_by' => 4,
                'society_id' => 3,
            ],
        ];

        foreach ($tickets as $ticket) {
            SupportTicket::create($ticket);
        }
    }
}
