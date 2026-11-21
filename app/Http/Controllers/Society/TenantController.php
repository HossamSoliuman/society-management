<?php

namespace App\Http\Controllers\Society;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreTenantRequest;
use App\Models\Member;
use App\Models\Society;
use App\Models\Tenant;
use App\Models\TenantAgreement;
use App\Models\Unit;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class TenantController extends Controller
{
    public function index(Request $request): View
    {
        $society = $this->currentSociety();
        $status = in_array($request->query('status'), ['active', 'moved_out'], true) ? $request->query('status') : 'active';

        $tenants = Tenant::query()
            ->forSociety($society)
            ->with(['unit', 'owner', 'currentAgreement'])
            ->where('status', $status)
            ->when($request->filled('verification'), fn ($q) => $q->where('police_verification', $request->string('verification')))
            ->when($request->filled('q'), function ($q) use ($request) {
                $term = $request->string('q');
                $q->where(fn ($sub) => $sub->where('name', 'like', "%{$term}%")
                    ->orWhere('mobile', 'like', "%{$term}%")
                    ->orWhereHas('unit', fn ($u) => $u->where('unit_number', 'like', "%{$term}%")));
            })
            ->orderBy('name')
            ->paginate(10)
            ->withQueryString();

        return view('society.tenants.index', [
            'tenants' => $tenants,
            'status' => $status,
            'stats' => [
                'active' => Tenant::query()->forSociety($society)->where('status', 'active')->count(),
                'expiring' => TenantAgreement::query()->forSociety($society)->expiringWithin()->count(),
                'expired' => TenantAgreement::query()->forSociety($society)->lapsed()->count(),
                'verification' => Tenant::query()->forSociety($society)->where('status', 'active')->where('police_verification', 'pending')->count(),
            ],
            'verifications' => Tenant::VERIFICATION,
        ]);
    }

    public function create(Request $request): View
    {
        $society = $this->currentSociety();

        return view('society.tenants.create', $this->formData($society) + [
            'tenant' => new Tenant([
                'unit_id' => $request->integer('unit') ?: null,
                'occupants_count' => 1,
                'move_in_date' => Carbon::today(),
                'police_verification' => 'pending',
            ]),
        ]);
    }

    public function store(StoreTenantRequest $request): RedirectResponse
    {
        $society = $this->currentSociety();
        $data = $request->validated();

        $tenant = DB::transaction(function () use ($society, $data, $request): Tenant {
            $tenant = Tenant::create($this->tenantAttributes($data) + [
                'society_id' => $society->id,
                'status' => 'active',
            ]);

            if (! empty($data['agreement_start_date'])) {
                TenantAgreement::create([
                    'society_id' => $society->id,
                    'tenant_id' => $tenant->id,
                    'unit_id' => $tenant->unit_id,
                    'agreement_number' => $data['agreement_number'] ?? null,
                    'start_date' => $data['agreement_start_date'],
                    'end_date' => $data['agreement_end_date'],
                    'monthly_rent' => $data['monthly_rent'] ?? 0,
                    'security_deposit' => $data['security_deposit'] ?? 0,
                    'document_path' => $request->hasFile('agreement_document')
                        ? $request->file('agreement_document')->store("tenant-agreements/{$society->id}", 'local')
                        : null,
                    'status' => 'active',
                ]);
            }

            $this->occupyUnit($tenant);

            return $tenant;
        });

        return redirect()->route('society.tenants.show', $tenant)->with('success', "Tenant {$tenant->name} added.");
    }

    public function show(Tenant $tenant): View
    {
        return view('society.tenants.show', [
            'tenant' => $tenant->load(['unit', 'owner', 'agreements']),
        ]);
    }

    public function edit(Tenant $tenant): View
    {
        return view('society.tenants.edit', $this->formData($this->currentSociety()) + ['tenant' => $tenant]);
    }

    public function update(StoreTenantRequest $request, Tenant $tenant): RedirectResponse
    {
        $previousUnit = $tenant->unit_id;

        DB::transaction(function () use ($request, $tenant, $previousUnit) {
            $tenant->update($this->tenantAttributes($request->validated()));

            if ($tenant->isActive() && $previousUnit !== $tenant->unit_id) {
                $this->vacateUnit(Unit::find($previousUnit), $tenant);
                $this->occupyUnit($tenant);
            }
        });

        return redirect()->route('society.tenants.show', $tenant)->with('success', 'Tenant details updated.');
    }

    /**
     * Mark the tenant as moved out, end the running agreement and free the unit.
     */
    public function moveOut(Request $request, Tenant $tenant): RedirectResponse
    {
        abort_unless($tenant->isActive(), 404);
        $data = $request->validate(['move_out_date' => ['required', 'date']]);

        DB::transaction(function () use ($tenant, $data) {
            $tenant->update(['status' => 'moved_out', 'move_out_date' => $data['move_out_date']]);
            $tenant->agreements()->where('status', 'active')->update(['status' => 'terminated', 'terminated_at' => now()]);
            $this->vacateUnit($tenant->unit, $tenant);
        });

        return redirect()->route('society.tenants.show', $tenant)->with('success', "{$tenant->name} marked as moved out.");
    }

    public function destroy(Tenant $tenant): RedirectResponse
    {
        DB::transaction(function () use ($tenant) {
            if ($tenant->isActive()) {
                $this->vacateUnit($tenant->unit, $tenant);
            }
            $tenant->delete();
        });

        return redirect()->route('society.tenants.index')->with('success', 'Tenant record deleted.');
    }

    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    private function tenantAttributes(array $data): array
    {
        return collect($data)->only([
            'name', 'mobile', 'email', 'unit_id', 'owner_member_id', 'occupants_count', 'id_proof_type',
            'id_proof_number', 'permanent_address', 'move_in_date', 'police_verification', 'notes',
        ])->all();
    }

    private function occupyUnit(Tenant $tenant): void
    {
        $tenant->unit?->update([
            'status' => 'occupied',
            'occupied_by_name' => $tenant->name,
            'occupied_by_role' => 'Tenant',
        ]);
    }

    /**
     * Free the unit only if this tenant is the one recorded as its occupant.
     */
    private function vacateUnit(?Unit $unit, Tenant $tenant): void
    {
        if ($unit && $unit->occupied_by_role === 'Tenant' && $unit->occupied_by_name === $tenant->getOriginal('name')) {
            $unit->update(['status' => 'vacant', 'occupied_by_name' => null, 'occupied_by_role' => null]);
        }
    }

    /**
     * @return array{units: Collection<int, Unit>, owners: Collection<int, Member>, idProofs: array<int, string>, verifications: array<string, string>}
     */
    private function formData(Society $society): array
    {
        return [
            'units' => Unit::query()->forSociety($society)->orderBy('wing')->orderBy('unit_number')->get(['id', 'unit_number', 'wing', 'building', 'status', 'member_id']),
            'owners' => Member::query()->forSociety($society)->where('member_type', 'owner')->orderBy('name')->get(['id', 'name', 'flat_unit']),
            'idProofs' => Tenant::ID_PROOFS,
            'verifications' => Tenant::VERIFICATION,
        ];
    }
}
