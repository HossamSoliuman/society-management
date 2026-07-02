<?php

namespace Database\Seeders;

use App\Models\Society;
use App\Models\SupportRequest;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;

class SupportRequestSeeder extends Seeder
{
    /**
     * Seed the 8 exact rows shown on "Priority support.png" (PS-2024-048 … -041), then
     * fill to 48 total so the pagination footer ("of 48 requests") is real.
     */
    public function run(): void
    {
        $societyId = Society::orderBy('id')->value('id');

        // [request_id, subject, category, raised_by_name, flat_no, priority, status, raised_at]
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

        foreach ($named as [$requestId, $subject, $category, $name, $flat, $priority, $status, $raisedAt]) {
            $request = SupportRequest::create([
                'society_id' => $societyId,
                'request_id' => $requestId,
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
            $request->forceFill(['created_at' => $timestamp, 'updated_at' => $timestamp])->saveQuietly();
        }

        // Fill to 48 total with older, lower-numbered requests.
        for ($n = 40; $n >= 1; $n--) {
            $raisedAt = Carbon::parse('2024-05-21 12:00:00')->subDays(40 - $n);
            $request = SupportRequest::factory()->create([
                'society_id' => $societyId,
                'request_id' => 'PS-2024-'.str_pad((string) $n, 3, '0', STR_PAD_LEFT),
                'raised_at' => $raisedAt,
            ]);
            $request->forceFill(['created_at' => $raisedAt, 'updated_at' => $raisedAt])->saveQuietly();
        }
    }
}
