<?php

namespace App\Http\Controllers\Society;

use App\Exports\VendorsExport;
use App\Http\Controllers\Controller;
use App\Models\ExpenseCategory;
use App\Models\Society;
use App\Models\Vendor;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Validation\Rule;
use Illuminate\View\View;
use Maatwebsite\Excel\Facades\Excel;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class VendorController extends Controller
{
    public function index(Request $request): View
    {
        $society = $this->currentSociety();

        $vendors = $this->filteredVendors($request, $society)->paginate(10)->withQueryString();

        $base = Vendor::query()->when($society, fn ($q) => $q->where('society_id', $society->id));

        return view('society.expenses.vendors.index', [
            'society' => $society,
            'vendors' => $vendors,
            'categories' => $this->categories($society),
            'stats' => [
                'total' => (clone $base)->count(),
                'active' => (clone $base)->where('status', 'active')->count(),
                'inactive' => (clone $base)->where('status', 'inactive')->count(),
                'top' => (clone $base)->orderBy('name')->value('name') ?? '—',
            ],
        ]);
    }

    public function export(Request $request): BinaryFileResponse
    {
        $society = $this->currentSociety();

        return Excel::download(
            new VendorsExport($this->filteredVendors($request, $society)),
            $this->exportFileName('vendors', society: $society),
        );
    }

    /**
     * Vendor list query with the list screen's search and filters applied.
     */
    private function filteredVendors(Request $request, Society $society): Builder
    {
        return Vendor::query()
            ->with('category')
            ->where('society_id', $society->id)
            ->when($request->filled('status'), fn ($q) => $q->where('status', $request->string('status')))
            ->when($request->filled('category'), fn ($q) => $q->where('category_id', $request->integer('category')))
            ->when($request->filled('q'), function ($q) use ($request) {
                $term = $request->string('q');
                $q->where(function ($sub) use ($term) {
                    $sub->where('name', 'like', "%{$term}%")
                        ->orWhere('company', 'like', "%{$term}%")
                        ->orWhere('phone', 'like', "%{$term}%")
                        ->orWhere('email', 'like', "%{$term}%");
                });
            })
            ->orderBy('name');
    }

    public function create(): View
    {
        $society = $this->currentSociety();

        return view('society.expenses.vendors.create', [
            'categories' => $this->categories($society),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $society = $this->currentSociety();
        $data = $this->validateVendor($request);

        $vendor = Vendor::create($data + ['society_id' => $society?->id]);

        return redirect()->route('society.expenses.vendors.index')
            ->with('success', "Vendor \"{$vendor->name}\" created successfully.");
    }

    public function edit(Vendor $vendor): View
    {
        $society = $this->currentSociety();

        return view('society.expenses.vendors.edit', [
            'vendor' => $vendor,
            'categories' => $this->categories($society),
        ]);
    }

    public function update(Request $request, Vendor $vendor): RedirectResponse
    {
        $data = $this->validateVendor($request);
        $vendor->update($data);

        return redirect()->route('society.expenses.vendors.index')
            ->with('success', "Vendor \"{$vendor->name}\" updated successfully.");
    }

    public function destroy(Vendor $vendor): RedirectResponse
    {
        $name = $vendor->name;
        $vendor->delete();

        return redirect()->route('society.expenses.vendors.index')
            ->with('success', "Vendor \"{$name}\" deleted.");
    }

    /**
     * @return array<string, mixed>
     */
    private function validateVendor(Request $request): array
    {
        return $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'company' => ['nullable', 'string', 'max:255'],
            'phone' => ['nullable', 'string', 'max:255'],
            'email' => ['nullable', 'email', 'max:255'],
            'gst_number' => ['nullable', 'string', 'max:255'],
            'category_id' => ['nullable', 'integer', Rule::exists('expense_categories', 'id')->where('society_id', $this->currentSociety()->id)],
            'address' => ['nullable', 'string', 'max:500'],
            'status' => ['required', 'in:active,inactive'],
            'notes' => ['nullable', 'string', 'max:500'],
        ]);
    }

    /**
     * @return Collection<int, ExpenseCategory>
     */
    private function categories(?Society $society)
    {
        return ExpenseCategory::query()
            ->when($society, fn ($q) => $q->where('society_id', $society->id))
            ->orderBy('display_order')
            ->orderBy('name')
            ->get();
    }
}
