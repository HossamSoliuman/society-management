<?php

namespace App\Http\Controllers\Society;

use App\Http\Controllers\Controller;
use App\Models\ExpenseCategory;
use App\Models\Society;
use App\Models\Vendor;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\View\View;

class VendorController extends Controller
{
    public function index(Request $request): View
    {
        $society = Society::first();

        $vendors = Vendor::query()
            ->with('category')
            ->when($society, fn ($q) => $q->where('society_id', $society->id))
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
            ->orderBy('name')
            ->paginate(10)
            ->withQueryString();

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

    public function create(): View
    {
        $society = Society::first();

        return view('society.expenses.vendors.create', [
            'categories' => $this->categories($society),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $society = Society::first();
        $data = $this->validateVendor($request);

        $vendor = Vendor::create($data + ['society_id' => $society?->id]);

        return redirect()->route('society.expenses.vendors.index')
            ->with('success', "Vendor \"{$vendor->name}\" created successfully.");
    }

    public function edit(Vendor $vendor): View
    {
        $society = Society::first();

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
            'category_id' => ['nullable', 'integer', 'exists:expense_categories,id'],
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
