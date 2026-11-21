<?php

namespace Database\Seeders;

use App\Models\Society;
use App\Models\Staff;
use App\Models\StaffAttendance;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;

/**
 * Society staff for societies that have units registered, with attendance
 * marked for every day of the current month up to yesterday.
 */
class StaffSeeder extends Seeder
{
    /** @var array<int, array{0: string, 1: string, 2: string, 3: int}> name, role, shift, salary */
    private const PEOPLE = [
        ['Ramesh Yadav', 'Security Supervisor', 'general', 24000],
        ['Suresh Pawar', 'Security Guard', 'morning', 16000],
        ['Dinesh Gaikwad', 'Security Guard', 'evening', 16000],
        ['Mahesh Shinde', 'Security Guard', 'night', 17000],
        ['Kavita More', 'Housekeeping', 'morning', 13000],
        ['Sunita Jadhav', 'Housekeeping', 'morning', 13000],
        ['Anil Kamble', 'Housekeeping', 'evening', 13000],
        ['Prakash Patil', 'Electrician', 'general', 21000],
        ['Vijay Salve', 'Plumber', 'general', 20000],
        ['Ganesh Bhosale', 'Gardener', 'morning', 14000],
        ['Rahul Thorat', 'Lift Operator', 'general', 15000],
        ['Meena Kulkarni', 'Office Assistant', 'general', 18000],
    ];

    public function run(): void
    {
        $societies = Society::query()->has('units')->doesntHave('staff')->get();
        $today = Carbon::today();

        foreach ($societies as $society) {
            foreach (self::PEOPLE as $i => [$name, $role, $shift, $salary]) {
                $staff = Staff::create([
                    'society_id' => $society->id,
                    'employee_code' => 'EMP-'.str_pad((string) ($i + 1), 4, '0', STR_PAD_LEFT),
                    'name' => $name,
                    'role' => $role,
                    'employment_type' => str_contains($role, 'Security') ? 'agency' : 'society',
                    'shift' => $shift,
                    'mobile' => '97'.str_pad((string) (31000000 + $i * 7919), 8, '0', STR_PAD_LEFT),
                    'emergency_contact' => 'Family · 98'.str_pad((string) (41000000 + $i * 911), 8, '0', STR_PAD_LEFT),
                    'id_proof_type' => 'Aadhaar',
                    'id_proof_number' => '5821 '.str_pad((string) (3300 + $i), 4, '0', STR_PAD_LEFT).' '.str_pad((string) (6100 + $i * 3), 4, '0', STR_PAD_LEFT),
                    'joining_date' => $today->copy()->subMonths(6 + $i * 3),
                    'monthly_salary' => $salary,
                    'status' => 'active',
                ]);

                for ($day = $today->copy()->startOfMonth(); $day->lt($today); $day->addDay()) {
                    $roll = ($i * 31 + $day->day * 17) % 20;
                    $status = match (true) {
                        $day->isSunday() && $roll < 12 => 'leave',
                        $roll === 0 => 'absent',
                        $roll === 1 => 'half_day',
                        $roll === 2 => 'leave',
                        default => 'present',
                    };

                    StaffAttendance::create([
                        'society_id' => $society->id,
                        'staff_id' => $staff->id,
                        'date' => $day->toDateString(),
                        'status' => $status,
                        'check_in' => in_array($status, ['present', 'half_day'], true) ? ['morning' => '06:55', 'evening' => '14:05', 'night' => '21:58', 'general' => '09:02'][$shift] : null,
                        'check_out' => $status === 'present' ? ['morning' => '15:00', 'evening' => '22:10', 'night' => '07:05', 'general' => '18:10'][$shift] : null,
                    ]);
                }
            }
        }
    }
}
