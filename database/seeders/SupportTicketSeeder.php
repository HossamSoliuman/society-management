<?php

namespace Database\Seeders;

use App\Models\Society;
use App\Models\SupportTicket;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;

class SupportTicketSeeder extends Seeder
{
    /**
     * Platform tickets raised by society admins plus the 48 society-side
     * "Priority Support" rows shown on the design (PS-2024-048 … -001).
     */
    public function run(): void
    {
        $platformTickets = [
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
                'raised_by_type' => 'staff_admin',
                'raised_by_name' => 'Society Admin',
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
                'raised_by_type' => 'staff_admin',
                'raised_by_name' => 'Society Admin',
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
                'raised_by_type' => 'staff_admin',
                'raised_by_name' => 'Society Admin',
            ],
        ];

        foreach ($platformTickets as $ticket) {
            // Demo ids only exist after the full DatabaseSeeder; skip gracefully otherwise.
            if (! Society::whereKey($ticket['society_id'])->exists() || ! User::whereKey($ticket['created_by'])->exists()) {
                continue;
            }
            $ticket['assigned_to'] = isset($ticket['assigned_to']) && User::whereKey($ticket['assigned_to'])->exists() ? $ticket['assigned_to'] : null;

            SupportTicket::firstOrCreate(['ticket_number' => $ticket['ticket_number']], $ticket + ['raised_at' => now()]);
        }

        $this->seedPrioritySupport();
    }

    private function seedPrioritySupport(): void
    {
        $societyId = Society::orderBy('id')->value('id');
        if (! $societyId) {
            return;
        }

        // [ticket_number, subject, category, raised_by_name, flat_no, priority, status, raised_at]
        $named = [
            ['PS-2024-048', 'Lift not working on 3rd floor', 'Lift', 'Rajesh Kumar', 'A-302', 'high', 'open', '2024-05-28 10:30:00'],
            ['PS-2024-047', 'Water leakage in bathroom ceiling', 'Maintenance', 'Priya Sharma', 'B-105', 'medium', 'in_progress', '2024-05-27 14:15:00'],
            ['PS-2024-046', 'Power fluctuation in Tower C', 'Electrical', 'Amit Verma', 'C-401', 'high', 'open', '2024-05-27 09:00:00'],
            ['PS-2024-045', 'Corridor not cleaned since two days', 'Housekeeping', 'Sunita Rao', 'A-201', 'low', 'resolved', '2024-05-26 16:45:00'],
            ['PS-2024-044', 'Gate access card not working', 'Access Control', 'Vikram Singh', 'B-303', 'medium', 'in_progress', '2024-05-25 11:20:00'],
            ['PS-2024-043', 'Garden sprinkler broken', 'Garden', 'Meena Iyer', 'C-102', 'low', 'resolved', '2024-05-24 08:30:00'],
            ['PS-2024-042', 'Suspicious person near parking', 'Security', 'Arjun Nair', 'A-405', 'high', 'closed', '2024-05-23 22:10:00'],
            ['PS-2024-041', 'Intercom static noise', 'Others', 'Kavita Desai', 'B-208', 'medium', 'resolved', '2024-05-22 13:00:00'],
        ];

        foreach ($named as [$number, $subject, $category, $name, $flat, $priority, $status, $raisedAt]) {
            $ticket = SupportTicket::firstOrCreate(['ticket_number' => $number], [
                'society_id' => $societyId,
                'subject' => $subject,
                'category' => $category,
                'raised_by_type' => 'member',
                'raised_by_name' => $name,
                'flat_no' => $flat,
                'mobile' => '+91 98765 43210',
                'email' => strtolower(str_replace(' ', '.', $name)).'@example.com',
                'preferred_contact' => 'Phone',
                'priority' => $priority,
                'status' => $status,
                'description' => $subject.'. Please look into this at the earliest.',
                'location' => 'Building '.substr($flat, 0, 1),
                'raised_at' => $raisedAt,
            ]);

            $timestamp = Carbon::parse($raisedAt);
            $ticket->forceFill(['created_at' => $timestamp, 'updated_at' => $timestamp])->saveQuietly();
        }

        for ($n = 40; $n >= 1; $n--) {
            $number = 'PS-2024-'.str_pad((string) $n, 3, '0', STR_PAD_LEFT);
            if (SupportTicket::where('ticket_number', $number)->exists()) {
                continue;
            }
            $raisedAt = Carbon::parse('2024-05-21 12:00:00')->subDays(40 - $n);
            $ticket = SupportTicket::factory()->create([
                'society_id' => $societyId,
                'ticket_number' => $number,
                'raised_at' => $raisedAt,
            ]);
            $ticket->forceFill(['created_at' => $raisedAt, 'updated_at' => $raisedAt])->saveQuietly();
        }
    }
}
