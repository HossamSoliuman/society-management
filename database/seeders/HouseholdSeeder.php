<?php

namespace Database\Seeders;

use App\Models\FamilyMember;
use App\Models\Member;
use App\Models\MemberRequest;
use App\Models\Society;
use App\Models\Unit;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;

/**
 * Family members for owner households and a few join / transfer requests
 * waiting for the office — for societies that have units registered.
 */
class HouseholdSeeder extends Seeder
{
    /** @var array<int, array{0: string, 1: string, 2: string, 3: int}> name prefix, relation, gender, age */
    private const FAMILY = [
        ['Sunita', 'Spouse', 'female', 41], ['Aarav', 'Son', 'male', 14], ['Diya', 'Daughter', 'female', 9],
        ['Kamala', 'Mother', 'female', 68], ['Rohan', 'Son', 'male', 22], ['Meera', 'Spouse', 'female', 35],
    ];

    public function run(): void
    {
        $societies = Society::query()->has('units')->get();
        $today = Carbon::today();

        foreach ($societies as $society) {
            if (! FamilyMember::query()->where('society_id', $society->id)->exists()) {
                $owners = Member::query()->where('society_id', $society->id)->where('member_type', 'owner')->where('status', 'active')->orderBy('id')->limit(40)->get();

                foreach ($owners as $i => $owner) {
                    $surname = str($owner->name)->afterLast(' ')->toString();
                    foreach (array_slice(self::FAMILY, $i % 3, 1 + $i % 3) as $j => [$first, $relation, $gender, $age]) {
                        FamilyMember::create([
                            'society_id' => $society->id,
                            'member_id' => $owner->id,
                            'name' => "{$first} {$surname}",
                            'relation' => $relation,
                            'gender' => $gender,
                            'date_of_birth' => $today->copy()->subYears($age)->subDays(($i * 37 + $j * 11) % 300),
                            'mobile' => $age >= 18 ? '91'.str_pad((string) (70000000 + $i * 913 + $j), 8, '0', STR_PAD_LEFT) : null,
                            'occupation' => match (true) {
                                $age < 18 => 'Student',
                                $age > 60 => 'Retired',
                                default => ['Engineer', 'Teacher', 'Doctor', 'Designer'][$i % 4],
                            },
                            'is_resident' => $relation !== 'Son' || $age < 20,
                        ]);
                    }
                }
            }

            if (! MemberRequest::query()->where('society_id', $society->id)->exists()) {
                $vacant = Unit::query()->where('society_id', $society->id)->where('status', 'vacant')->orderBy('id')->limit(3)->get();
                $owned = Unit::query()->where('society_id', $society->id)->where('status', 'occupied')->where('occupied_by_role', 'Owner')->orderByDesc('id')->first();

                $requests = [
                    ['join', 'owner', 'Harshad Kulkarni', $vacant[0] ?? null, 'Bought the flat last month; registration documents attached.'],
                    ['join', 'tenant', 'Sneha Iyer', $vacant[1] ?? null, 'Moving in with my family on an 11-month agreement.'],
                    ['transfer', 'owner', 'Farhan Qureshi', $owned, 'Purchased this flat from the previous owner. Sale deed registered.'],
                    ['join', 'family_member', 'Tanvi Rao', $vacant[2] ?? null, null],
                ];

                foreach ($requests as $i => [$type, $memberType, $name, $unit, $message]) {
                    if (! $unit) {
                        continue;
                    }
                    MemberRequest::create([
                        'society_id' => $society->id,
                        'request_type' => $type,
                        'member_type' => $memberType,
                        'name' => $name,
                        'mobile' => '93'.str_pad((string) (21000000 + $i * 7177), 8, '0', STR_PAD_LEFT),
                        'email' => str($name)->lower()->replace(' ', '.').'@example.com',
                        'unit_id' => $unit->id,
                        'unit_number' => $unit->unit_number,
                        'move_in_date' => $today->copy()->addDays(7 + $i * 5),
                        'message' => $message,
                        'status' => 'pending',
                    ]);
                }
            }
        }
    }
}
