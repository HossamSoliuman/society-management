<?php

namespace App\Http\Controllers\Society;

use App\Http\Controllers\Controller;
use App\Models\Society;
use App\Models\Unit;
use Illuminate\Http\Request;

class UnitController extends Controller
{
    public function index(Request $request)
    {
        $society = Society::firstOrFail();

        $query = $society->units()->orderBy('unit_number');

        if ($search = $request->input('q')) {
            $query->where(function ($q) use ($search) {
                $q->where('unit_number', 'like', "%{$search}%")
                    ->orWhere('owner_name', 'like', "%{$search}%")
                    ->orWhere('owner_mobile', 'like', "%{$search}%")
                    ->orWhere('occupied_by_name', 'like', "%{$search}%");
            });
        }

        foreach (['building', 'wing', 'floor', 'status'] as $filter) {
            if ($value = $request->input($filter)) {
                $query->where($filter, $value);
            }
        }

        if ($type = $request->input('type')) {
            $query->where('unit_type', $type);
        }

        $units = $query->paginate(8)->withQueryString();

        $stats = [
            'total' => $society->units()->count(),
            'occupied' => $society->units()->where('status', 'occupied')->count(),
            'vacant' => $society->units()->where('status', 'vacant')->count(),
            'under_maintenance' => $society->units()->where('status', 'under_maintenance')->count(),
        ];

        $buildings = $society->units()->whereNotNull('building')->distinct()->orderBy('building')->pluck('building');
        $wings = $society->units()->whereNotNull('wing')->distinct()->orderBy('wing')->pluck('wing');
        $floors = $society->units()->whereNotNull('floor')->distinct()->orderBy('floor')->pluck('floor');
        $types = $society->units()->whereNotNull('unit_type')->distinct()->orderBy('unit_type')->pluck('unit_type');

        return view('society.units.index', compact('society', 'units', 'stats', 'buildings', 'wings', 'floors', 'types'));
    }

    public function create()
    {
        $society = Society::firstOrFail();

        return view('society.units.create', compact('society'));
    }

    public function store(Request $request)
    {
        $society = Society::firstOrFail();

        $validated = $this->validateUnit($request);

        $society->units()->create($validated);

        return redirect()->route('society.units.index')->with('success', 'Unit added successfully.');
    }

    public function importForm()
    {
        $society = Society::firstOrFail();

        return view('society.units.import', compact('society'));
    }

    public function import(Request $request)
    {
        $request->validate([
            'file' => 'required|file|mimes:xlsx,xls,csv|max:5120',
        ]);

        return redirect()->route('society.units.index')->with('success', 'Units imported successfully.');
    }

    public function show(Unit $unit)
    {
        return view('society.units.show', compact('unit'));
    }

    public function edit(Unit $unit)
    {
        return view('society.units.edit', compact('unit'));
    }

    public function update(Request $request, Unit $unit)
    {
        $validated = $this->validateUnit($request);

        $unit->update($validated);

        return redirect()->route('society.units.index')->with('success', 'Unit updated successfully.');
    }

    public function destroy(Unit $unit)
    {
        $unit->delete();

        return redirect()->route('society.units.index')->with('success', 'Unit deleted successfully.');
    }

    private function validateUnit(Request $request): array
    {
        return $request->validate([
            'unit_number' => 'required|string|max:50',
            'building' => 'nullable|string|max:100',
            'wing' => 'nullable|string|max:100',
            'floor' => 'nullable|string|max:100',
            'unit_type' => 'nullable|string|max:50',
            'area_sqft' => 'nullable|integer|min:0',
            'status' => 'required|in:occupied,vacant,under_maintenance',
            'occupied_by_name' => 'nullable|string|max:255',
            'occupied_by_role' => 'nullable|string|max:50',
            'owner_name' => 'nullable|string|max:255',
            'owner_mobile' => 'nullable|string|max:20',
        ]);
    }
}
