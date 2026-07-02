<?php

namespace App\Http\Controllers\Society;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreAssetCategoryRequest;
use App\Models\AssetCategory;
use App\Models\Society;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use Illuminate\View\View;

class AssetCategoryController extends Controller
{
    /** Selectable icons for a category: fa-class => [label, tint color]. */
    private const ICONS = [
        'fa-elevator' => ['Lift', 'blue'],
        'fa-charging-station' => ['Generator', 'gray'],
        'fa-faucet' => ['Water Pump', 'blue'],
        'fa-shield-halved' => ['Security', 'red'],
        'fa-fire-extinguisher' => ['Fire Safety', 'red'],
        'fa-droplet' => ['Water Tank', 'blue'],
        'fa-tower-broadcast' => ['Communication', 'blue'],
        'fa-leaf' => ['Garden', 'green'],
        'fa-car' => ['Parking', 'orange'],
        'fa-bolt' => ['Electrical', 'yellow'],
        'fa-fan' => ['HVAC', 'teal'],
        'fa-couch' => ['Furniture', 'orange'],
        'fa-computer' => ['IT Equipment', 'purple'],
        'fa-cube' => ['General', 'gray'],
    ];

    public function index(Request $request): View
    {
        $society = Society::first();

        $categories = AssetCategory::query()
            ->withCount('assets')
            ->when($society, fn ($q) => $q->where('society_id', $society->id))
            ->when($request->filled('status'), fn ($q) => $q->where('status', $request->string('status')))
            ->when($request->filled('q'), function ($q) use ($request) {
                $term = $request->string('q');
                $q->where(function ($sub) use ($term) {
                    $sub->where('name', 'like', "%{$term}%")
                        ->orWhere('code', 'like', "%{$term}%")
                        ->orWhere('description', 'like', "%{$term}%");
                });
            })
            ->orderBy('display_order')
            ->orderBy('name')
            ->paginate(10)
            ->withQueryString();

        $base = AssetCategory::query()->when($society, fn ($q) => $q->where('society_id', $society->id));
        $total = (clone $base)->count();
        $active = (clone $base)->where('status', 'active')->count();
        $inactive = (clone $base)->where('status', 'inactive')->count();
        $totalAssets = (clone $base)->withCount('assets')->get()->sum('assets_count');

        return view('society.assets.categories.index', [
            'society' => $society,
            'categories' => $categories,
            'stats' => [
                'total' => $total,
                'active' => $active,
                'inactive' => $inactive,
                'total_assets' => $totalAssets,
                'active_pct' => $total ? round($active / $total * 100, 2).'%' : '0%',
                'inactive_pct' => $total ? round($inactive / $total * 100, 2).'%' : '0%',
            ],
            'overview' => $this->overviewDonut($total, $active, $inactive),
        ]);
    }

    public function create(): View
    {
        $society = Society::first();

        return view('society.assets.categories.create', [
            'category' => null,
            'mode' => 'create',
            'action' => route('society.assets.categories.store'),
            'icons' => self::ICONS,
            'parents' => $this->parentOptions($society),
        ]);
    }

    public function store(StoreAssetCategoryRequest $request): RedirectResponse
    {
        $society = Society::first();
        $category = AssetCategory::create($this->payload($request->validated(), $society));

        return redirect()->route('society.assets.categories.index')
            ->with('success', "Category \"{$category->name}\" created successfully.");
    }

    public function edit(AssetCategory $category): View
    {
        $society = Society::first();

        return view('society.assets.categories.edit', [
            'category' => $category,
            'mode' => 'edit',
            'action' => route('society.assets.categories.update', $category),
            'icons' => self::ICONS,
            'parents' => $this->parentOptions($society, $category->id),
        ]);
    }

    public function update(StoreAssetCategoryRequest $request, AssetCategory $category): RedirectResponse
    {
        $category->update($this->payload($request->validated(), $category->society));

        return redirect()->route('society.assets.categories.index')
            ->with('success', "Category \"{$category->name}\" updated successfully.");
    }

    public function destroy(AssetCategory $category): RedirectResponse
    {
        $name = $category->name;
        $category->delete();

        return redirect()->route('society.assets.categories.index')
            ->with('success', "Category \"{$name}\" deleted.");
    }

    /**
     * Build persisted attributes from validated form data.
     *
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    private function payload(array $data, ?Society $society): array
    {
        $icon = $data['icon'] ?? 'fa-cube';

        return [
            'society_id' => $society?->id,
            'name' => $data['name'],
            'code' => Str::upper($data['code']),
            'icon' => $icon,
            'color' => self::ICONS[$icon][1] ?? 'gray',
            'description' => $data['description'] ?? null,
            'status' => $data['status'],
            'display_order' => $data['display_order'] ?? 0,
            'movable' => $this->requestBoolean('movable'),
            'immovable' => $this->requestBoolean('immovable'),
            'parent_id' => $data['parent_id'] ?? null,
            'asset_life_years' => $data['asset_life_years'] ?? null,
            'notes' => $data['notes'] ?? null,
        ];
    }

    private function requestBoolean(string $key): bool
    {
        return request()->boolean($key);
    }

    /**
     * @return Collection<int, AssetCategory>
     */
    private function parentOptions(?Society $society, ?int $exclude = null): Collection
    {
        return AssetCategory::query()
            ->when($society, fn ($q) => $q->where('society_id', $society->id))
            ->when($exclude, fn ($q) => $q->where('id', '!=', $exclude))
            ->orderBy('name')
            ->get();
    }

    /**
     * Category Overview donut (right rail on "Asset categories list.png").
     *
     * @return array<string, mixed>
     */
    private function overviewDonut(int $total, int $active, int $inactive): array
    {
        return [
            'center_value' => (string) $total,
            'center_label' => 'Total',
            'segments' => [
                ['label' => 'Active', 'value' => $active, 'color' => '#10B981', 'pct' => $total ? round($active / $total * 100, 2).'%' : '0%'],
                ['label' => 'Inactive', 'value' => $inactive, 'color' => '#EF4444', 'pct' => $total ? round($inactive / $total * 100, 2).'%' : '0%'],
            ],
        ];
    }
}
