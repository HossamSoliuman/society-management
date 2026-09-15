<?php

namespace App\Http\Controllers\Society;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreAmcCategoryRequest;
use App\Http\Requests\StoreAmcContractRequest;
use App\Models\AmcCategory;
use App\Models\AmcContract;
use App\Models\Asset;
use App\Models\ServiceVendor;
use App\Models\Society;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\View\View;

class AmcController extends Controller
{
    /** Contract types offered on the AMC form. */
    private const CONTRACT_TYPES = ['Comprehensive', 'Non-Comprehensive'];

    /** Icon choices for the AMC category picker (fa-… classes). */
    private const CATEGORY_ICONS = [
        'fa-elevator', 'fa-calendar-days', 'fa-bullhorn', 'fa-fire-extinguisher',
        'fa-droplet', 'fa-bolt', 'fa-phone', 'fa-snowflake',
    ];

    public function index(Request $request): View
    {
        $society = $this->currentSociety();

        $tab = $request->string('tab')->toString() ?: 'all';
        $tabStatus = match ($tab) {
            'expiring-soon' => 'expiring_soon',
            'expired' => 'expired',
            default => null,
        };

        $contracts = AmcContract::query()
            ->with('category')
            ->when($society, fn ($q) => $q->where('society_id', $society->id))
            ->when($tabStatus, fn ($q) => $q->where('status', $tabStatus))
            ->when($request->filled('category'), fn ($q) => $q->where('amc_category_id', $request->integer('category')))
            ->when($request->filled('vendor'), fn ($q) => $q->where('vendor_name', $request->string('vendor')))
            ->when($request->filled('status'), fn ($q) => $q->where('status', $request->string('status')))
            ->when($request->filled('q'), function ($q) use ($request) {
                $term = $request->string('q');
                $q->where(function ($sub) use ($term) {
                    $sub->where('item_asset', 'like', "%{$term}%")
                        ->orWhere('vendor_name', 'like', "%{$term}%")
                        ->orWhere('contract_no', 'like', "%{$term}%");
                });
            })
            ->when($tab === 'by-category', fn ($q) => $q->orderBy('amc_category_id'))
            ->when($tab === 'by-vendor', fn ($q) => $q->orderBy('vendor_name'))
            ->orderBy('end_date')
            ->paginate(8)
            ->withQueryString();

        return view('society.amc.index', [
            'tab' => $tab,
            'contracts' => $contracts,
            'stats' => $this->trackerStats($society),
            'categories' => $this->categoryOptions($society),
            'vendors' => $this->vendorNameOptions($society),
        ]);
    }

    public function create(): View
    {
        $society = $this->currentSociety();

        return view('society.amc.create', [
            'categories' => $this->categoryOptions($society),
            'vendors' => ServiceVendor::query()
                ->when($society, fn ($q) => $q->where('society_id', $society->id))
                ->orderBy('name')
                ->get(),
            'assets' => Asset::query()
                ->when($society, fn ($q) => $q->where('society_id', $society->id))
                ->orderBy('name')
                ->get(),
            'contractTypes' => self::CONTRACT_TYPES,
        ]);
    }

    public function store(StoreAmcContractRequest $request): RedirectResponse
    {
        $society = $this->currentSociety();
        $data = $request->validated();

        $vendorName = $data['vendor_name'] ?? null;
        if (! empty($data['service_vendor_id'])) {
            $vendorName = ServiceVendor::find($data['service_vendor_id'])?->name ?? $vendorName;
        }

        $status = ($data['action'] ?? 'save') === 'draft'
            ? 'draft'
            : $this->statusFromEndDate($data['end_date']);

        $contract = AmcContract::create([
            'society_id' => $society?->id,
            'item_asset' => $data['item_asset'],
            'item_sub' => $data['item_sub'] ?? null,
            'amc_category_id' => $data['amc_category_id'],
            'vendor_name' => $vendorName,
            'service_vendor_id' => $data['service_vendor_id'] ?? null,
            'contract_no' => $data['contract_no'] ?? null,
            'po_invoice_no' => $data['po_invoice_no'] ?? null,
            'contract_type' => $data['contract_type'],
            'start_date' => $data['start_date'],
            'end_date' => $data['end_date'],
            'duration_months' => $data['duration_months'] ?? null,
            'amount' => $data['amount'],
            'tax_percent' => $data['tax_percent'] ?? null,
            'renewal_reminder_days' => $data['renewal_reminder_days'] ?? 30,
            'description' => $data['description'] ?? null,
            'status' => $status,
        ]);

        return redirect()->route('society.amc.index')
            ->with('success', "AMC contract for {$contract->item_asset} saved successfully.");
    }

    public function categories(Request $request): View
    {
        $society = $this->currentSociety();

        $categories = AmcCategory::query()
            ->withCount('contracts')
            ->when($society, fn ($q) => $q->where('society_id', $society->id))
            ->when($request->filled('status'), fn ($q) => $q->where('status', $request->string('status')))
            ->when($request->filled('q'), function ($q) use ($request) {
                $term = $request->string('q');
                $q->where(function ($sub) use ($term) {
                    $sub->where('name', 'like', "%{$term}%")
                        ->orWhere('description', 'like', "%{$term}%");
                });
            })
            ->orderBy('id')
            ->paginate(12)
            ->withQueryString();

        return view('society.amc.categories', [
            'categories' => $categories,
            'stats' => $this->categoryStats($society),
        ]);
    }

    public function createCategory(): View
    {
        return view('society.amc.category-create', [
            'icons' => self::CATEGORY_ICONS,
            'applicableAssets' => ['Tower A', 'Tower B', 'Tower C', 'Clubhouse', 'Basement', 'Common Areas'],
        ]);
    }

    public function storeCategory(StoreAmcCategoryRequest $request): RedirectResponse
    {
        $society = $this->currentSociety();
        $data = $request->validated();

        $category = AmcCategory::create([
            'society_id' => $society?->id,
            'name' => $data['name'],
            'description' => $data['description'] ?? null,
            'icon' => $data['icon'],
            'assets_covered' => 0,
            'applicable_assets' => $data['applicable_assets'] ?? [],
            'default_reminder_days' => $data['default_reminder_days'] ?? 30,
            'default_duration_months' => $data['default_duration_months'] ?? 12,
            'tax_applicable' => (bool) ($data['tax_applicable'] ?? false),
            'notes' => $data['notes'] ?? null,
            'status' => $data['status'],
        ]);

        return redirect()->route('society.amc.categories')
            ->with('success', "Category {$category->name} created successfully.");
    }

    /* -------------------------------------------------------------------------
     |  Helpers
     |------------------------------------------------------------------------- */

    /** Derive a contract status from its end date. */
    private function statusFromEndDate(string $endDate): string
    {
        $end = Carbon::parse($endDate)->startOfDay();
        $today = Carbon::now()->startOfDay();

        if ($end->lessThan($today)) {
            return 'expired';
        }

        if ($today->diffInDays($end, false) <= 30) {
            return 'expiring_soon';
        }

        return 'active';
    }

    /**
     * @return Collection<int, AmcCategory>
     */
    private function categoryOptions(?Society $society)
    {
        return AmcCategory::query()
            ->when($society, fn ($q) => $q->where('society_id', $society->id))
            ->orderBy('name')
            ->get();
    }

    /**
     * @return array<int, string>
     */
    private function vendorNameOptions(?Society $society): array
    {
        return AmcContract::query()
            ->when($society, fn ($q) => $q->where('society_id', $society->id))
            ->whereNotNull('vendor_name')
            ->distinct()
            ->orderBy('vendor_name')
            ->pluck('vendor_name')
            ->all();
    }

    /* -------------------------------------------------------------------------
     |  Stat cards (computed)
     |------------------------------------------------------------------------- */

    /**
     * @return array<string, string>
     */
    private function trackerStats(?Society $society): array
    {
        $rows = AmcContract::query()
            ->when($society, fn ($q) => $q->where('society_id', $society->id))
            ->selectRaw('status, COUNT(*) as c, COALESCE(SUM(amount),0) as v')
            ->groupBy('status')
            ->get()
            ->keyBy('status');

        $total = (int) $rows->sum('c');
        $active = (int) ($rows['active']->c ?? 0);

        return [
            'total' => number_format($total),
            'active' => number_format($active),
            'active_pct' => ($total > 0 ? round($active / $total * 100) : 0).'% of total',
            'expiring_soon' => number_format((int) ($rows['expiring_soon']->c ?? 0)),
            'expired' => number_format((int) ($rows['expired']->c ?? 0)),
            'total_value' => number_format((float) $rows->sum('v')),
        ];
    }

    /**
     * @return array<string, string>
     */
    private function categoryStats(?Society $society): array
    {
        $base = AmcCategory::query()->when($society, fn ($q) => $q->where('society_id', $society->id));
        $total = (clone $base)->count();
        $active = (clone $base)->where('status', 'active')->count();
        $inactive = $total - $active;
        $pct = fn (int $n) => ($total > 0 ? round($n / $total * 100, 1) : 0).'% of total';

        return [
            'total' => number_format($total),
            'active' => number_format($active),
            'active_pct' => $pct($active),
            'inactive' => number_format($inactive),
            'inactive_pct' => $pct($inactive),
            'assets_covered' => number_format((int) AmcContract::query()->when($society, fn ($q) => $q->where('society_id', $society->id))->whereIn('status', ['active', 'expiring_soon'])->count()),
        ];
    }
}
