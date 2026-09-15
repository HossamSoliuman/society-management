<?php

namespace App\Http\Controllers\Member;

use App\Http\Requests\StoreVehicleRequest;
use App\Models\Vehicle;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class VehicleController extends PortalController
{
    public function index(): View
    {
        $member = $this->currentMember();

        return view('member.vehicles.index', [
            'member' => $member,
            'vehicles' => $member->vehicles()->with('unit')->orderBy('registration_no')->get(),
        ]);
    }

    public function create(): View
    {
        $member = $this->currentMember();

        return view('member.vehicles.create', [
            'member' => $member,
            'types' => Vehicle::TYPES,
            'units' => $member->units()->orderBy('unit_number')->get(),
        ]);
    }

    public function store(StoreVehicleRequest $request): RedirectResponse
    {
        $member = $this->currentMember();
        $data = $request->validated();

        $member->vehicles()->create($data + [
            'society_id' => $member->society_id,
            'owner_name' => $data['owner_name'] ?? $member->name,
            'status' => $data['status'] ?? 'active',
        ]);

        return redirect()->route('member.vehicles.index')->with('success', 'Vehicle added.');
    }

    public function edit(Vehicle $vehicle): View
    {
        $this->ownedByMember($vehicle->member_id);
        $member = $this->currentMember();

        return view('member.vehicles.edit', [
            'member' => $member,
            'vehicle' => $vehicle,
            'types' => Vehicle::TYPES,
            'units' => $member->units()->orderBy('unit_number')->get(),
        ]);
    }

    public function update(StoreVehicleRequest $request, Vehicle $vehicle): RedirectResponse
    {
        $this->ownedByMember($vehicle->member_id);

        $vehicle->update($request->validated());

        return redirect()->route('member.vehicles.index')->with('success', 'Vehicle updated.');
    }

    public function destroy(Vehicle $vehicle): RedirectResponse
    {
        $this->ownedByMember($vehicle->member_id);

        $vehicle->delete();

        return redirect()->route('member.vehicles.index')->with('success', 'Vehicle removed.');
    }
}
