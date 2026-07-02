<?php

namespace App\Http\Controllers\Society;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreAssetRequest;
use App\Models\Asset;
use App\Models\AssetCategory;
use App\Models\Society;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\View\View;

class AssetController extends Controller
{
    /** Status options for the Add/Edit form and the status filter. */
    private const STATUSES = [
        'in_use' => 'In Use',
        'under_maintenance' => 'Under Maintenance',
        'inactive' => 'Inactive',
        'disposed' => 'Disposed',
    ];

    /** Condition options for the Add/Edit form. */
    private const CONDITIONS = [
        'good' => 'Good',
        'fair' => 'Fair',
        'poor' => 'Poor',
    ];

    private const TOWERS = ['Tower A', 'Tower B', 'Tower C', 'Clubhouse'];

    private const FLOORS = ['Basement', 'Ground', '1st', '2nd', '3rd', '4th', 'Terrace'];

    private const DEPRECIATION_METHODS = ['Straight Line', 'Written Down Value', 'None'];

    private const USAGE_TYPES = ['Common Area', 'Dedicated', 'Shared'];

    public function index(Request $request): View
    {
        $society = Society::first();

        $assets = Asset::query()
            ->with(['category', 'images'])
            ->when($society, fn ($q) => $q->where('society_id', $society->id))
            ->when($request->filled('category'), fn ($q) => $q->where('category_id', $request->integer('category')))
            ->when($request->filled('location'), fn ($q) => $q->where('location', 'like', '%'.$request->string('location').'%'))
            ->when($request->filled('status'), fn ($q) => $q->where('status', $request->string('status')))
            ->when($request->filled('q'), function ($q) use ($request) {
                $term = $request->string('q');
                $q->where(function ($sub) use ($term) {
                    $sub->where('name', 'like', "%{$term}%")
                        ->orWhere('asset_code', 'like', "%{$term}%")
                        ->orWhere('brand', 'like', "%{$term}%");
                });
            })
            ->orderBy('asset_code')
            ->paginate(8)
            ->withQueryString();

        return view('society.assets.index', [
            'society' => $society,
            'assets' => $assets,
            'stats' => $this->stats(),
            'categories' => $this->categories($society),
            'railCategories' => $this->railCategories($society),
            'locations' => self::TOWERS,
            'statuses' => self::STATUSES,
        ]);
    }

    public function create(): View
    {
        $society = Society::first();

        return view('society.assets.create', $this->formData($society) + [
            'asset' => null,
            'mode' => 'create',
            'action' => route('society.assets.store'),
            'nextCode' => $this->nextCode(),
        ]);
    }

    public function store(StoreAssetRequest $request): RedirectResponse
    {
        $society = Society::first();
        $asset = Asset::create($this->payload($request->validated(), $society));

        $this->storeImages($request, $asset);

        return redirect()->route('society.assets.index')
            ->with('success', "Asset \"{$asset->name}\" ({$asset->asset_code}) saved successfully.");
    }

    public function edit(Asset $asset): View
    {
        $society = Society::first();

        return view('society.assets.edit', $this->formData($society) + [
            'asset' => $asset,
            'mode' => 'edit',
            'action' => route('society.assets.update', $asset),
            'nextCode' => $asset->asset_code,
        ]);
    }

    public function update(StoreAssetRequest $request, Asset $asset): RedirectResponse
    {
        $society = Society::first();
        $asset->update($this->payload($request->validated(), $society));

        $this->storeImages($request, $asset);

        return redirect()->route('society.assets.index')
            ->with('success', "Asset \"{$asset->name}\" updated successfully.");
    }

    public function destroy(Asset $asset): RedirectResponse
    {
        $code = $asset->asset_code;
        $asset->delete();

        return redirect()->route('society.assets.index')
            ->with('success', "Asset {$code} deleted.");
    }

    public function import(): RedirectResponse
    {
        return redirect()->route('society.assets.index')
            ->with('success', 'Asset import is coming soon.');
    }

    /**
     * Shared select-option data for the Add/Edit form.
     *
     * @return array<string, mixed>
     */
    private function formData(?Society $society): array
    {
        return [
            'categories' => $this->categories($society),
            'statuses' => self::STATUSES,
            'conditions' => self::CONDITIONS,
            'towers' => self::TOWERS,
            'floors' => self::FLOORS,
            'depreciationMethods' => self::DEPRECIATION_METHODS,
            'usageTypes' => self::USAGE_TYPES,
        ];
    }

    /**
     * Build persisted attributes from validated form data.
     *
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    private function payload(array $data, ?Society $society): array
    {
        return [
            'society_id' => $society?->id,
            'name' => $data['name'],
            'asset_code' => $data['asset_code'],
            'category_id' => $data['category_id'],
            'brand' => $data['brand'] ?? null,
            'model' => $data['model'] ?? null,
            'serial_number' => $data['serial_number'] ?? null,
            'description' => $data['description'] ?? null,
            'location' => $data['location'],
            'tower_wing' => $data['tower_wing'] ?? null,
            'floor' => $data['floor'] ?? null,
            'area_room' => $data['area_room'] ?? null,
            'assigned_to' => $data['assigned_to'] ?? null,
            'vendor_supplier' => $data['vendor_supplier'] ?? null,
            'purchase_from' => $data['purchase_from'] ?? null,
            'purchase_date' => $data['purchase_date'],
            'purchase_cost' => $data['purchase_cost'],
            'warranty_start' => $data['warranty_start'] ?? null,
            'warranty_end' => $data['warranty_end'] ?? null,
            'invoice_no' => $data['invoice_no'] ?? null,
            'invoice_date' => $data['invoice_date'] ?? null,
            'expected_life_years' => $data['expected_life_years'] ?? null,
            'depreciation_method' => $data['depreciation_method'] ?? null,
            'status' => $data['status'],
            'condition' => $data['condition'],
            'usage_type' => $data['usage_type'] ?? null,
            'qr_code' => $data['qr_code'] ?? null,
            'notes' => $data['notes'] ?? null,
            'current_value' => $data['purchase_cost'],
        ];
    }

    private function storeImages(StoreAssetRequest $request, Asset $asset): void
    {
        if (! $request->hasFile('images')) {
            return;
        }

        $hasPrimary = $asset->images()->where('is_primary', true)->exists();

        foreach ($request->file('images') as $index => $file) {
            $asset->images()->create([
                'path' => $file->store('asset-images', 'public'),
                'is_primary' => ! $hasPrimary && $index === 0,
            ]);
        }
    }

    private function nextCode(): string
    {
        $next = (Asset::max('id') ?? 0) + 1;

        return 'AST'.str_pad((string) $next, 4, '0', STR_PAD_LEFT);
    }

    /**
     * @return Collection<int, AssetCategory>
     */
    private function categories(?Society $society): Collection
    {
        return AssetCategory::query()
            ->when($society, fn ($q) => $q->where('society_id', $society->id))
            ->where('status', 'active')
            ->orderBy('display_order')
            ->orderBy('name')
            ->get();
    }

    /**
     * Right-rail "Asset Categories" list — categories that hold at least one asset.
     *
     * @return Collection<int, AssetCategory>
     */
    private function railCategories(?Society $society): Collection
    {
        return AssetCategory::query()
            ->when($society, fn ($q) => $q->where('society_id', $society->id))
            ->withCount('assets')
            ->has('assets')
            ->orderByDesc('assets_count')
            ->get();
    }

    /**
     * Demo stat-card figures matching "Assets Management.png".
     *
     * @return array<string, mixed>
     */
    private function stats(): array
    {
        return [
            'total' => 128,
            'in_use' => 98,
            'in_use_pct' => '76.56%',
            'under_maintenance' => 12,
            'under_maintenance_pct' => '9.38%',
            'inactive' => 18,
            'inactive_pct' => '14.06%',
            'total_value' => '24,75,800',
        ];
    }
}
