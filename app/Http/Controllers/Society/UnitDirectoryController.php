<?php

namespace App\Http\Controllers\Society;

use App\Http\Controllers\Controller;
use App\Models\MaintenanceBill;
use App\Models\Member;
use App\Models\Tenant;
use App\Models\Unit;
use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * Read-only views over the unit register: who owns each unit, and how the
 * society is occupied wing by wing.
 */
class UnitDirectoryController extends Controller
{
    /** Occupancy filters for the owners register. */
    private const OCCUPANCY = ['self' => 'Self-occupied', 'rented' => 'Rented out', 'vacant' => 'Vacant', 'under_maintenance' => 'Under Maintenance'];

    public function owners(Request $request): View
    {
        $society = $this->currentSociety();
        $occupancy = array_key_exists((string) $request->query('occupancy'), self::OCCUPANCY) ? (string) $request->query('occupancy') : null;

        $units = Unit::query()
            ->forSociety($society)
            ->with('member')
            ->when($request->filled('wing'), fn ($q) => $q->where('wing', $request->string('wing')))
            ->when($occupancy === 'self', fn ($q) => $q->where('status', 'occupied')->where(fn ($s) => $s->whereNull('occupied_by_role')->orWhere('occupied_by_role', '!=', 'Tenant')))
            ->when($occupancy === 'rented', fn ($q) => $q->where('status', 'occupied')->where('occupied_by_role', 'Tenant'))
            ->when(in_array($occupancy, ['vacant', 'under_maintenance'], true), fn ($q) => $q->where('status', $occupancy))
            ->when($request->filled('q'), function ($q) use ($request) {
                $term = $request->string('q');
                $q->where(fn ($sub) => $sub->where('unit_number', 'like', "%{$term}%")
                    ->orWhere('owner_name', 'like', "%{$term}%")
                    ->orWhere('owner_mobile', 'like', "%{$term}%"));
            })
            ->orderBy('wing')
            ->orderBy('unit_number')
            ->paginate(15)
            ->withQueryString();

        $unitNumbers = $units->pluck('unit_number')->all();
        $ownerMembers = Member::query()
            ->forSociety($society)
            ->where('member_type', 'owner')
            ->whereIn('flat_unit', $unitNumbers)
            ->get()
            ->keyBy('flat_unit');

        $unitIds = $units->pluck('id')->all();
        $dues = MaintenanceBill::query()
            ->where('society_id', $society->id)
            ->whereIn('unit_id', $unitIds)
            ->where('outstanding_amount', '>', 0)
            ->selectRaw('unit_id, SUM(outstanding_amount) as due')
            ->groupBy('unit_id')
            ->pluck('due', 'unit_id');
        $tenants = Tenant::query()
            ->where('society_id', $society->id)
            ->where('status', 'active')
            ->whereIn('unit_id', $unitIds)
            ->get(['id', 'unit_id', 'name'])
            ->keyBy('unit_id');

        $all = Unit::query()->forSociety($society);

        return view('society.units.owners', [
            'units' => $units,
            'ownerMembers' => $ownerMembers,
            'dues' => $dues,
            'tenants' => $tenants,
            'wings' => (clone $all)->whereNotNull('wing')->distinct()->orderBy('wing')->pluck('wing'),
            'occupancyOptions' => self::OCCUPANCY,
            'occupancy' => $occupancy,
            'stats' => [
                'owners' => (clone $all)->whereNotNull('owner_name')->distinct()->count('owner_name'),
                'self' => (clone $all)->where('status', 'occupied')->where(fn ($s) => $s->whereNull('occupied_by_role')->orWhere('occupied_by_role', '!=', 'Tenant'))->count(),
                'rented' => (clone $all)->where('status', 'occupied')->where('occupied_by_role', 'Tenant')->count(),
                'missing' => (clone $all)->whereNull('owner_name')->whereNull('member_id')->count(),
            ],
        ]);
    }

    public function occupancy(Request $request): View
    {
        $society = $this->currentSociety();
        $status = in_array($request->query('status'), ['occupied', 'vacant', 'under_maintenance'], true) ? (string) $request->query('status') : null;

        $units = Unit::query()
            ->forSociety($society)
            ->when($request->filled('wing'), fn ($q) => $q->where('wing', $request->string('wing')))
            ->orderBy('wing')
            ->orderBy('unit_number')
            ->get(['id', 'unit_number', 'wing', 'floor', 'unit_type', 'status', 'occupied_by_name', 'occupied_by_role', 'owner_name']);

        $all = Unit::query()->forSociety($society)->get(['wing', 'status', 'occupied_by_role']);
        $summary = $all->groupBy(fn (Unit $u) => $u->wing ?: 'Unassigned')->map(fn ($group) => [
            'total' => $group->count(),
            'owner' => $group->where('status', 'occupied')->where('occupied_by_role', '!=', 'Tenant')->count(),
            'tenant' => $group->where('status', 'occupied')->where('occupied_by_role', 'Tenant')->count(),
            'vacant' => $group->where('status', 'vacant')->count(),
            'maintenance' => $group->where('status', 'under_maintenance')->count(),
        ])->sortKeys();

        $total = $all->count();
        $occupied = $all->where('status', 'occupied')->count();

        return view('society.units.occupancy', [
            'wingsSummary' => $summary,
            'grid' => $units->groupBy(fn (Unit $u) => $u->wing ?: 'Unassigned')
                ->map(fn ($group) => $group->groupBy(fn (Unit $u) => $u->floor ?: '—')->sortKeysUsing(fn ($a, $b) => ((int) $b) <=> ((int) $a))),
            'status' => $status,
            'wings' => $summary->keys(),
            'stats' => [
                'total' => $total,
                'occupied' => $occupied,
                'tenant' => $all->where('status', 'occupied')->where('occupied_by_role', 'Tenant')->count(),
                'vacant' => $all->where('status', 'vacant')->count(),
                'maintenance' => $all->where('status', 'under_maintenance')->count(),
                'rate' => $total > 0 ? round($occupied / $total * 100) : 0,
            ],
        ]);
    }
}
