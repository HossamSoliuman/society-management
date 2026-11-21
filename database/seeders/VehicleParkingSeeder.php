<?php

namespace Database\Seeders;

use App\Models\Member;
use App\Models\ParkingAllotment;
use App\Models\ParkingSlot;
use App\Models\Society;
use App\Models\Unit;
use App\Models\Vehicle;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;

/**
 * Residents' vehicles, a basement car deck and a stilt bike deck, with most
 * vehicles allotted a slot — for societies that have units registered.
 */
class VehicleParkingSeeder extends Seeder
{
    /** @var array<int, array{0: string, 1: string, 2: string}> make, model, type */
    private const MODELS = [
        ['Maruti', 'Swift', 'car'], ['Hyundai', 'Creta', 'car'], ['Honda', 'City', 'car'], ['Tata', 'Nexon', 'car'],
        ['Honda', 'Activa', 'scooter'], ['Bajaj', 'Pulsar', 'bike'], ['Mahindra', 'XUV700', 'car'], ['TVS', 'Jupiter', 'scooter'],
        ['Kia', 'Seltos', 'car'], ['Royal Enfield', 'Classic 350', 'bike'],
    ];

    private const COLOURS = ['White', 'Silver', 'Grey', 'Red', 'Blue', 'Black'];

    public function run(): void
    {
        $societies = Society::query()->has('units')->doesntHave('parkingSlots')->get();
        $start = Carbon::today()->subMonths(8)->startOfMonth();

        foreach ($societies as $society) {
            $carSlots = collect(range(1, 80))->map(fn (int $n) => ParkingSlot::create([
                'society_id' => $society->id,
                'slot_number' => 'B1-'.str_pad((string) $n, 3, '0', STR_PAD_LEFT),
                'area' => 'Basement 1',
                'slot_type' => $n > 76 ? 'ev' : 'car',
                'is_covered' => true,
                'status' => $n === 40 ? 'blocked' : 'available',
            ]));
            $bikeSlots = collect(range(1, 40))->map(fn (int $n) => ParkingSlot::create([
                'society_id' => $society->id,
                'slot_number' => 'S-'.str_pad((string) $n, 3, '0', STR_PAD_LEFT),
                'area' => 'Stilt',
                'slot_type' => 'bike',
                'is_covered' => true,
                'status' => 'available',
            ]));

            $owners = Member::query()->where('society_id', $society->id)->whereIn('member_type', ['owner', 'tenant'])->where('status', 'active')->orderBy('id')->limit(90)->get();
            $car = 0;
            $bike = 0;

            foreach ($owners as $i => $owner) {
                [$make, $model, $type] = self::MODELS[$i % count(self::MODELS)];
                $unit = $owner->flat_unit ? Unit::query()->where('society_id', $society->id)->where('unit_number', $owner->flat_unit)->first() : null;
                $registration = sprintf('MH 46 %s%s %04d', chr(65 + $i % 26), chr(65 + ($i * 7) % 26), 1000 + ($i * 373) % 9000);
                if (Vehicle::query()->where('society_id', $society->id)->where('registration_no', $registration)->exists()) {
                    continue;
                }

                $vehicle = Vehicle::create([
                    'society_id' => $society->id,
                    'member_id' => $owner->id,
                    'unit_id' => $unit?->id,
                    'registration_no' => $registration,
                    'vehicle_type' => $type,
                    'make' => $make,
                    'model' => $model,
                    'color' => self::COLOURS[$i % count(self::COLOURS)],
                    'owner_name' => $owner->name,
                    'rfid_tag' => 'GM'.str_pad((string) (5000 + $i), 6, '0', STR_PAD_LEFT),
                    'status' => 'active',
                ]);

                // Leave every seventh vehicle on the waiting list.
                if (! $unit || $i % 7 === 6) {
                    continue;
                }

                $slot = $vehicle->isTwoWheeler() ? $bikeSlots->get($bike++) : $carSlots->filter(fn (ParkingSlot $s) => $s->status === 'available' && $s->slot_type === 'car')->values()->get($car++);
                if (! $slot) {
                    continue;
                }

                ParkingAllotment::create([
                    'society_id' => $society->id,
                    'parking_slot_id' => $slot->id,
                    'unit_id' => $unit->id,
                    'member_id' => $owner->id,
                    'vehicle_id' => $vehicle->id,
                    'start_date' => $start->copy()->addDays($i * 3),
                    'monthly_charge' => $vehicle->isTwoWheeler() ? 200 : 500,
                    'status' => 'active',
                ]);
            }
        }
    }
}
