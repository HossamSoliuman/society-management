<?php

namespace Database\Seeders;

use App\Models\Amenity;
use App\Models\AmenityBooking;
use App\Models\Member;
use App\Models\Society;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;

/**
 * Clubhouse, party hall, courts and a guest room, with a spread of past,
 * confirmed and pending bookings — for societies that have units registered.
 */
class AmenitySeeder extends Seeder
{
    /** @var array<int, array<string, mixed>> */
    private const AMENITIES = [
        ['name' => 'Clubhouse', 'location' => 'Ground floor, Tower A', 'capacity' => 60, 'open_time' => '07:00', 'close_time' => '22:00', 'max_hours' => 4, 'charge_per_hour' => 500, 'requires_approval' => true],
        ['name' => 'Party Hall', 'location' => 'Podium level', 'capacity' => 120, 'open_time' => '10:00', 'close_time' => '23:00', 'max_hours' => 6, 'charge_per_hour' => 1200, 'requires_approval' => true],
        ['name' => 'Badminton Court', 'location' => 'Sports complex', 'capacity' => 4, 'open_time' => '06:00', 'close_time' => '22:00', 'max_hours' => 2, 'charge_per_hour' => 0, 'requires_approval' => false],
        ['name' => 'Tennis Court', 'location' => 'Behind Tower C', 'capacity' => 4, 'open_time' => '06:00', 'close_time' => '21:00', 'max_hours' => 2, 'charge_per_hour' => 0, 'requires_approval' => false],
        ['name' => 'Guest Room', 'location' => 'Tower B, 1st floor', 'capacity' => 3, 'open_time' => '08:00', 'close_time' => '20:00', 'max_hours' => 12, 'charge_per_hour' => 150, 'requires_approval' => true],
    ];

    public function run(): void
    {
        $societies = Society::query()->has('units')->doesntHave('amenities')->get();
        $today = Carbon::today();

        foreach ($societies as $society) {
            $amenities = collect(self::AMENITIES)->map(fn (array $a) => Amenity::create($a + ['society_id' => $society->id, 'status' => 'active']));
            $residents = Member::query()->where('society_id', $society->id)->where('member_type', 'owner')->where('status', 'active')->orderBy('id')->limit(30)->get();
            if ($residents->isEmpty()) {
                continue;
            }

            $n = 0;
            for ($offset = -20; $offset <= 12; $offset += 2) {
                foreach ($amenities as $k => $amenity) {
                    if (($offset + $k) % 3 !== 0) {
                        continue;
                    }
                    $member = $residents[$n++ % $residents->count()];
                    $startHour = max((int) substr($amenity->open_time, 0, 2), 9 + ($n % 6));
                    $hours = min($amenity->max_hours, 1 + $n % 3);
                    $date = $today->copy()->addDays($offset);
                    $status = match (true) {
                        $offset < 0 => $n % 9 === 0 ? 'cancelled' : 'approved',
                        $amenity->requires_approval && $n % 3 === 0 => 'pending',
                        default => 'approved',
                    };

                    AmenityBooking::create([
                        'society_id' => $society->id,
                        'amenity_id' => $amenity->id,
                        'member_id' => $member->id,
                        'unit_id' => $member->primaryUnit()?->id,
                        'booking_date' => $date->toDateString(),
                        'start_time' => sprintf('%02d:00', $startHour),
                        'end_time' => sprintf('%02d:00', $startHour + $hours),
                        'guests' => $amenity->capacity > 10 ? 10 + $n % 30 : $n % 3,
                        'purpose' => $amenity->capacity > 10 ? ['Birthday party', 'Family get-together', 'Society meeting', 'Anniversary dinner'][$n % 4] : null,
                        'charge' => $hours * (float) $amenity->charge_per_hour,
                        'status' => $status,
                        'decided_at' => $status === 'pending' ? null : $date->copy()->subDays(3),
                    ]);
                }
            }
        }
    }
}
