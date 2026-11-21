<?php

namespace Database\Seeders;

use App\Models\Society;
use App\Models\Unit;
use App\Models\Visitor;
use App\Models\VisitorPreApproval;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;

/**
 * Two weeks of gate traffic (with a few visitors still inside) and a set of
 * upcoming passes, for societies that have units registered.
 */
class VisitorSeeder extends Seeder
{
    /** @var array<int, array{0: string, 1: string, 2: string|null}> name, purpose, company */
    private const VISITORS = [
        ['Rohit Malhotra', 'guest', null], ['Swiggy Rider', 'delivery', 'Swiggy'], ['Amazon Courier', 'delivery', 'Amazon'],
        ['Uber Driver', 'cab', 'Uber'], ['Imran Shaikh', 'service', 'Urban Company'], ['Lata Bai', 'domestic_help', null],
        ['Pooja Mehra', 'guest', null], ['Zomato Rider', 'delivery', 'Zomato'], ['AC Technician', 'service', 'Blue Star'],
        ['Kiran Desai', 'guest', null], ['Flipkart Courier', 'delivery', 'Flipkart'], ['Ola Driver', 'cab', 'Ola'],
    ];

    public function run(): void
    {
        $societies = Society::query()->has('units')->whereDoesntHave('visitors')->get();
        $now = Carbon::now();

        foreach ($societies as $society) {
            $units = Unit::query()->where('society_id', $society->id)->where('status', 'occupied')->orderBy('id')->limit(60)->get();
            if ($units->isEmpty()) {
                continue;
            }

            $n = 0;
            for ($daysAgo = 13; $daysAgo >= 0; $daysAgo--) {
                $perDay = 4 + ($daysAgo * 7) % 5;
                for ($k = 0; $k < $perDay; $k++, $n++) {
                    [$name, $purpose, $company] = self::VISITORS[$n % count(self::VISITORS)];
                    $checkIn = $now->copy()->subDays($daysAgo)->setTime(8 + ($n * 3) % 12, ($n * 13) % 60);
                    if ($checkIn->isFuture()) {
                        continue;
                    }
                    $stay = match ($purpose) {
                        'delivery', 'cab' => 6 + $n % 10,
                        'service' => 45 + $n % 60,
                        'domestic_help' => 120 + $n % 90,
                        default => 60 + $n % 180,
                    };
                    $checkOut = $checkIn->copy()->addMinutes($stay);
                    $stillInside = $daysAgo === 0 && $checkOut->isFuture();

                    Visitor::create([
                        'society_id' => $society->id,
                        'unit_id' => $units[$n % $units->count()]->id,
                        'name' => $name,
                        'mobile' => '98'.str_pad((string) (50000000 + $n * 7331), 8, '0', STR_PAD_LEFT),
                        'purpose' => $purpose,
                        'company' => $company,
                        'people_count' => $purpose === 'guest' ? 1 + $n % 3 : 1,
                        'vehicle_no' => in_array($purpose, ['cab', 'guest'], true) && $n % 2 === 0 ? 'MH 46 '.chr(65 + $n % 26).chr(66 + $n % 24).' '.str_pad((string) (1000 + $n * 37 % 9000), 4, '0', STR_PAD_LEFT) : null,
                        'check_in_at' => $checkIn,
                        'check_out_at' => $stillInside ? null : $checkOut,
                    ]);
                }
            }

            foreach (['Anjali Rao', 'Vikas Gupta', 'Neelam Joshi', 'Pest Control Team', 'Sanjay Kulkarni'] as $i => $guest) {
                $expected = $now->copy()->addHours(2 + $i * 7)->minute(0)->second(0);
                VisitorPreApproval::create([
                    'society_id' => $society->id,
                    'unit_id' => $units[($i * 11) % $units->count()]->id,
                    'visitor_name' => $guest,
                    'mobile' => '99'.str_pad((string) (61000000 + $i * 4113), 8, '0', STR_PAD_LEFT),
                    'purpose' => $i === 3 ? 'service' : 'guest',
                    'people_count' => 1 + $i % 3,
                    'expected_at' => $expected,
                    'valid_until' => $expected->copy()->addHours(24),
                    'pass_code' => VisitorPreApproval::freshCode($society->id),
                    'status' => 'pending',
                ]);
            }
        }
    }
}
