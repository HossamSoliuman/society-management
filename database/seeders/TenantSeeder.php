<?php

namespace Database\Seeders;

use App\Models\Member;
use App\Models\Tenant;
use App\Models\TenantAgreement;
use App\Models\Unit;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;

/**
 * Tenant records (with rental agreements) for units the unit register already
 * shows as tenant-occupied. Agreement end dates are spread so the register
 * shows running, expiring-soon and lapsed agreements.
 */
class TenantSeeder extends Seeder
{
    public function run(): void
    {
        $units = Unit::query()
            ->where('occupied_by_role', 'Tenant')
            ->whereNotNull('occupied_by_name')
            ->whereDoesntHave('society.tenants')
            ->orderBy('society_id')
            ->orderBy('id')
            ->get()
            ->groupBy('society_id');

        $today = Carbon::today();
        $verifications = ['verified', 'verified', 'pending', 'verified', 'not_required'];
        $endOffsets = [180, 12, 95, -20, 240, 25, 300, 60, -45, 150];

        foreach ($units as $societyUnits) {
            foreach ($societyUnits->take(40)->values() as $i => $unit) {
                $owner = Member::query()->where('society_id', $unit->society_id)->where('member_type', 'owner')->where('flat_unit', $unit->unit_number)->first();
                $endDate = $today->copy()->addDays($endOffsets[$i % count($endOffsets)]);
                $startDate = $endDate->copy()->subMonths(11)->addDay();
                $rent = [18000, 22000, 25000, 28000, 32000][$i % 5];

                $tenant = Tenant::create([
                    'society_id' => $unit->society_id,
                    'unit_id' => $unit->id,
                    'owner_member_id' => $owner?->id,
                    'name' => $unit->occupied_by_name,
                    'mobile' => '98'.str_pad((string) (20000000 + $unit->id * 37), 8, '0', STR_PAD_LEFT),
                    'email' => str($unit->occupied_by_name)->lower()->replace(' ', '.').'@example.com',
                    'occupants_count' => 1 + $i % 4,
                    'id_proof_type' => $i % 3 === 0 ? 'Passport' : 'Aadhaar',
                    'id_proof_number' => $i % 3 === 0 ? 'P'.str_pad((string) (4512000 + $unit->id), 7, '0', STR_PAD_LEFT) : '4521 '.str_pad((string) (1000 + $unit->id), 4, '0', STR_PAD_LEFT).' '.str_pad((string) (7000 + $i), 4, '0', STR_PAD_LEFT),
                    'move_in_date' => $startDate,
                    'police_verification' => $verifications[$i % count($verifications)],
                    'status' => 'active',
                ]);

                TenantAgreement::create([
                    'society_id' => $unit->society_id,
                    'tenant_id' => $tenant->id,
                    'unit_id' => $unit->id,
                    'agreement_number' => 'RA/'.$startDate->format('Y').'/'.str_pad((string) ($i + 1), 3, '0', STR_PAD_LEFT),
                    'start_date' => $startDate,
                    'end_date' => $endDate,
                    'monthly_rent' => $rent,
                    'security_deposit' => $rent * 3,
                    'status' => 'active',
                ]);
            }
        }
    }
}
