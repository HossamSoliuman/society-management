<?php

namespace App\Http\Controllers\Society;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreVehicleRequest;
use App\Models\Member;
use App\Models\Society;
use App\Models\Unit;
use App\Models\Vehicle;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * Society-wide vehicle register (residents also add their own from the portal).
 */
class VehicleController extends Controller
{
    public function index(Request $request): View
    {
        $society = $this->currentSociety();

        $vehicles = Vehicle::query()
            ->forSociety($society)
            ->with(['unit', 'member', 'allotment.slot'])
            ->when($request->filled('type'), fn ($q) => $q->where('vehicle_type', $request->string('type')))
            ->when($request->filled('status'), fn ($q) => $q->where('status', $request->string('status')))
            ->when($request->query('parking') === 'none', fn ($q) => $q->whereDoesntHave('allotment'))
            ->when($request->query('parking') === 'allotted', fn ($q) => $q->whereHas('allotment'))
            ->when($request->filled('q'), function ($q) use ($request) {
                $term = $request->string('q');
                $q->where(fn ($sub) => $sub->where('registration_no', 'like', "%{$term}%")
                    ->orWhere('owner_name', 'like', "%{$term}%")
                    ->orWhere('rfid_tag', 'like', "%{$term}%")
                    ->orWhereHas('unit', fn ($u) => $u->where('unit_number', 'like', "%{$term}%")));
            })
            ->orderBy('registration_no')
            ->paginate(15)
            ->withQueryString();

        $base = Vehicle::query()->forSociety($society)->where('status', 'active');

        return view('society.vehicles.index', [
            'vehicles' => $vehicles,
            'types' => Vehicle::TYPES,
            'stats' => [
                'total' => (clone $base)->count(),
                'cars' => (clone $base)->where('vehicle_type', 'car')->count(),
                'two_wheelers' => (clone $base)->whereIn('vehicle_type', ['bike', 'scooter'])->count(),
                'unparked' => (clone $base)->whereIn('vehicle_type', ['car', 'bike', 'scooter'])->whereDoesntHave('allotment')->count(),
            ],
        ]);
    }

    public function create(): View
    {
        return view('society.vehicles.create', $this->formData($this->currentSociety()) + [
            'vehicle' => new Vehicle(['vehicle_type' => 'car', 'status' => 'active']),
        ]);
    }

    public function store(StoreVehicleRequest $request): RedirectResponse
    {
        $society = $this->currentSociety();
        $data = $request->validated();

        $vehicle = Vehicle::create($data + [
            'society_id' => $society->id,
            'status' => $data['status'] ?? 'active',
            'owner_name' => ($data['owner_name'] ?? null) ?: (! empty($data['member_id']) ? Member::find($data['member_id'])?->name : null),
        ]);

        return redirect()->route('society.vehicles.index')->with('success', "Vehicle {$vehicle->registration_no} registered.");
    }

    public function edit(Vehicle $vehicle): View
    {
        return view('society.vehicles.edit', $this->formData($this->currentSociety()) + ['vehicle' => $vehicle->load('allotment.slot')]);
    }

    public function update(StoreVehicleRequest $request, Vehicle $vehicle): RedirectResponse
    {
        $data = $request->validated();
        $vehicle->update($data + ['status' => $data['status'] ?? $vehicle->status]);

        return redirect()->route('society.vehicles.index')->with('success', "Vehicle {$vehicle->registration_no} updated.");
    }

    public function destroy(Vehicle $vehicle): RedirectResponse
    {
        $vehicle->allotment()->update(['status' => 'ended', 'end_date' => now()->toDateString()]);
        $vehicle->delete();

        return redirect()->route('society.vehicles.index')->with('success', "Vehicle {$vehicle->registration_no} removed.");
    }

    /**
     * @return array<string, mixed>
     */
    private function formData(Society $society): array
    {
        return [
            'types' => Vehicle::TYPES,
            'units' => Unit::query()->forSociety($society)->orderBy('wing')->orderBy('unit_number')->get(['id', 'unit_number', 'wing']),
            'members' => Member::query()->forSociety($society)->where('status', 'active')->whereIn('member_type', ['owner', 'tenant'])->orderBy('name')->get(['id', 'name', 'flat_unit']),
        ];
    }
}
