<?php

namespace App\Http\Controllers\Society;

use App\Http\Controllers\Controller;
use App\Models\Member;
use App\Models\ParkingAllotment;
use App\Models\ParkingSlot;
use App\Models\Unit;
use App\Models\Vehicle;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

/**
 * Parking slots and their allotment to units/vehicles. A slot holds at most
 * one running allotment, and a vehicle at most one slot.
 */
class ParkingController extends Controller
{
    public function slots(Request $request): View
    {
        $society = $this->currentSociety();
        $state = in_array($request->query('state'), ['available', 'allotted', 'blocked'], true) ? (string) $request->query('state') : null;

        $slots = ParkingSlot::query()
            ->forSociety($society)
            ->with(['activeAllotment.unit', 'activeAllotment.vehicle'])
            ->when($request->filled('area'), fn ($q) => $q->where('area', $request->string('area')))
            ->when($request->filled('type'), fn ($q) => $q->where('slot_type', $request->string('type')))
            ->when($state === 'available', fn ($q) => $q->free())
            ->when($state === 'allotted', fn ($q) => $q->whereHas('allotments', fn ($a) => $a->where('status', 'active')))
            ->when($state === 'blocked', fn ($q) => $q->where('status', 'blocked'))
            ->when($request->filled('q'), fn ($q) => $q->where('slot_number', 'like', '%'.$request->string('q').'%'))
            ->orderBy('area')
            ->orderBy('slot_number')
            ->paginate(20)
            ->withQueryString();

        $base = ParkingSlot::query()->forSociety($society);

        return view('society.parking.slots', [
            'slots' => $slots,
            'types' => ParkingSlot::TYPES,
            'areas' => (clone $base)->whereNotNull('area')->distinct()->orderBy('area')->pluck('area'),
            'state' => $state,
            'stats' => [
                'total' => (clone $base)->count(),
                'free' => (clone $base)->free()->count(),
                'allotted' => (clone $base)->whereHas('allotments', fn ($a) => $a->where('status', 'active'))->count(),
                'blocked' => (clone $base)->where('status', 'blocked')->count(),
            ],
        ]);
    }

    public function storeSlot(Request $request): RedirectResponse
    {
        $society = $this->currentSociety();
        $data = $request->validate([
            'slot_number' => ['required', 'string', 'max:30', Rule::unique('parking_slots', 'slot_number')->where('society_id', $society->id)],
            'area' => ['nullable', 'string', 'max:60'],
            'slot_type' => ['required', Rule::in(array_keys(ParkingSlot::TYPES))],
            'is_covered' => ['nullable', 'boolean'],
            'count' => ['nullable', 'integer', 'min:1', 'max:200'],
        ]);

        $count = (int) ($data['count'] ?? 1);
        $created = DB::transaction(function () use ($society, $data, $count, $request): int {
            $created = 0;
            [$prefix, $start, $width] = $this->numberParts($data['slot_number']);

            for ($i = 0; $i < $count; $i++) {
                $number = $count === 1 ? $data['slot_number'] : $prefix.str_pad((string) ($start + $i), $width, '0', STR_PAD_LEFT);
                if (ParkingSlot::query()->where('society_id', $society->id)->where('slot_number', $number)->exists()) {
                    continue;
                }
                ParkingSlot::create([
                    'society_id' => $society->id,
                    'slot_number' => strtoupper($number),
                    'area' => $data['area'] ?? null,
                    'slot_type' => $data['slot_type'],
                    'is_covered' => $request->boolean('is_covered'),
                    'status' => 'available',
                ]);
                $created++;
            }

            return $created;
        });

        return redirect()->route('society.parking.slots')->with('success', "{$created} slot(s) added.");
    }

    public function toggleSlot(ParkingSlot $slot): RedirectResponse
    {
        if ($slot->status === 'blocked') {
            $slot->update(['status' => 'available']);

            return back()->with('success', "Slot {$slot->slot_number} reopened.");
        }

        if ($slot->activeAllotment()->exists()) {
            return back()->with('error', "Slot {$slot->slot_number} is allotted. End the allotment before blocking it.");
        }
        $slot->update(['status' => 'blocked']);

        return back()->with('success', "Slot {$slot->slot_number} blocked.");
    }

    public function destroySlot(ParkingSlot $slot): RedirectResponse
    {
        if ($slot->activeAllotment()->exists()) {
            return back()->with('error', "Slot {$slot->slot_number} is allotted. End the allotment first.");
        }

        $slot->delete();

        return back()->with('success', "Slot {$slot->slot_number} deleted.");
    }

    public function allotments(Request $request): View
    {
        $society = $this->currentSociety();
        $status = $request->query('status') === 'ended' ? 'ended' : 'active';

        $allotments = ParkingAllotment::query()
            ->forSociety($society)
            ->with(['slot', 'unit', 'member', 'vehicle'])
            ->where('status', $status)
            ->when($request->filled('q'), function ($q) use ($request) {
                $term = $request->string('q');
                $q->where(fn ($sub) => $sub->whereHas('slot', fn ($s) => $s->where('slot_number', 'like', "%{$term}%"))
                    ->orWhereHas('unit', fn ($u) => $u->where('unit_number', 'like', "%{$term}%"))
                    ->orWhereHas('vehicle', fn ($v) => $v->where('registration_no', 'like', "%{$term}%")));
            })
            ->latest('start_date')
            ->paginate(20)
            ->withQueryString();

        return view('society.parking.allotments', [
            'allotments' => $allotments,
            'status' => $status,
            'freeSlots' => ParkingSlot::query()->forSociety($society)->free()->orderBy('area')->orderBy('slot_number')->get(),
            'units' => Unit::query()->forSociety($society)->orderBy('wing')->orderBy('unit_number')->get(['id', 'unit_number']),
            'vehicles' => Vehicle::query()->forSociety($society)->where('status', 'active')->whereDoesntHave('allotment')->with('unit')->orderBy('registration_no')->get(),
            'monthlyIncome' => (float) ParkingAllotment::query()->forSociety($society)->where('status', 'active')->sum('monthly_charge'),
        ]);
    }

    public function storeAllotment(Request $request): RedirectResponse
    {
        $society = $this->currentSociety();
        $data = $request->validate([
            'parking_slot_id' => ['required', Rule::exists('parking_slots', 'id')->where('society_id', $society->id)],
            'unit_id' => ['required', Rule::exists('units', 'id')->where('society_id', $society->id)->whereNull('deleted_at')],
            'vehicle_id' => ['nullable', Rule::exists('vehicles', 'id')->where('society_id', $society->id)],
            'start_date' => ['required', 'date'],
            'monthly_charge' => ['nullable', 'numeric', 'min:0', 'max:100000'],
            'notes' => ['nullable', 'string', 'max:255'],
        ], [], ['parking_slot_id' => 'slot', 'unit_id' => 'unit', 'vehicle_id' => 'vehicle']);

        $allotment = DB::transaction(function () use ($society, $data): ParkingAllotment {
            $slot = ParkingSlot::query()->whereKey($data['parking_slot_id'])->lockForUpdate()->firstOrFail();
            $vehicle = ! empty($data['vehicle_id']) ? Vehicle::find($data['vehicle_id']) : null;

            if ($slot->status === 'blocked' || $slot->allotments()->where('status', 'active')->exists()) {
                throw ValidationException::withMessages(['parking_slot_id' => "Slot {$slot->slot_number} is no longer free."]);
            }
            if ($vehicle && $vehicle->allotment()->exists()) {
                throw ValidationException::withMessages(['vehicle_id' => "{$vehicle->registration_no} already has a parking slot."]);
            }
            if ($vehicle && $vehicle->isTwoWheeler() !== ($slot->slot_type === 'bike')) {
                throw ValidationException::withMessages(['vehicle_id' => "{$vehicle->typeLabel()} cannot be parked in a {$slot->typeLabel()} slot."]);
            }

            $unit = Unit::find($data['unit_id']);
            $member = $vehicle?->member ?? ($unit?->member ?? Member::query()->where('society_id', $society->id)->where('flat_unit', $unit?->unit_number)->where('member_type', 'owner')->first());

            return ParkingAllotment::create([
                'society_id' => $society->id,
                'parking_slot_id' => $slot->id,
                'unit_id' => $data['unit_id'],
                'member_id' => $member?->id,
                'vehicle_id' => $vehicle?->id,
                'start_date' => $data['start_date'],
                'monthly_charge' => $data['monthly_charge'] ?? 0,
                'notes' => $data['notes'] ?? null,
                'status' => 'active',
            ]);
        });

        return redirect()->route('society.parking.allotments')
            ->with('success', "Slot {$allotment->slot->slot_number} allotted to {$allotment->unit?->unit_number}.");
    }

    public function endAllotment(ParkingAllotment $allotment): RedirectResponse
    {
        abort_unless($allotment->status === 'active', 404);

        $allotment->update(['status' => 'ended', 'end_date' => now()->toDateString()]);

        return back()->with('success', "Allotment of slot {$allotment->slot?->slot_number} ended.");
    }

    /**
     * Split "B1-007" into ["B1-", 7, 3] so a run of slots can be numbered.
     *
     * @return array{0: string, 1: int, 2: int}
     */
    private function numberParts(string $slotNumber): array
    {
        if (preg_match('/^(.*?)(\d+)$/', strtoupper($slotNumber), $m)) {
            return [$m[1], (int) $m[2], strlen($m[2])];
        }

        return [strtoupper($slotNumber).'-', 1, 2];
    }
}
