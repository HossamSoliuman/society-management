<?php

namespace App\Http\Controllers\Society;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreServiceVendorRequest;
use App\Models\ServiceVendor;
use App\Models\Society;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ServiceVendorController extends Controller
{
    /** Vendor service categories offered across the create form and filters. */
    private const CATEGORIES = [
        'Electrical', 'Housekeeping', 'Security', 'Pest Control',
        'Maintenance', 'Waste Management', 'Plumbing', 'Landscaping',
        'Lift Maintenance', 'Fire Safety',
    ];

    /** Payment term presets for the create form dropdown. */
    private const PAYMENT_TERMS = ['Advance', 'Net 15', 'Net 30', 'Net 45', 'Net 60', 'On Delivery'];

    /** Indian states for the address dropdown. */
    private const STATES = [
        'Andhra Pradesh', 'Delhi', 'Gujarat', 'Karnataka', 'Kerala',
        'Madhya Pradesh', 'Maharashtra', 'Punjab', 'Rajasthan', 'Tamil Nadu',
        'Telangana', 'Uttar Pradesh', 'West Bengal',
    ];

    public function index(Request $request): View
    {
        $society = Society::first();

        $vendors = ServiceVendor::query()
            ->when($society, fn ($q) => $q->where('society_id', $society->id))
            ->when($request->filled('category'), fn ($q) => $q->where('category', $request->string('category')))
            ->when($request->filled('status'), fn ($q) => $q->where('status', $request->string('status')))
            ->when($request->filled('approval_status'), fn ($q) => $q->where('approval_status', $request->string('approval_status')))
            ->when($request->filled('q'), function ($q) use ($request) {
                $term = $request->string('q');
                $q->where(function ($sub) use ($term) {
                    $sub->where('name', 'like', "%{$term}%")
                        ->orWhere('company', 'like', "%{$term}%")
                        ->orWhere('contact_person', 'like', "%{$term}%")
                        ->orWhere('phone', 'like', "%{$term}%")
                        ->orWhere('email', 'like', "%{$term}%");
                });
            })
            ->orderBy('vendor_code')
            ->orderBy('id')
            ->paginate(10)
            ->withQueryString();

        return view('society.vendors.index', [
            'active' => 'vendors',
            'vendors' => $vendors,
            'stats' => $this->vendorStats(),
            'categories' => self::CATEGORIES,
        ]);
    }

    public function create(): View
    {
        return view('society.vendors.create', [
            'active' => 'vendors',
            'vendor' => new ServiceVendor,
            'categories' => self::CATEGORIES,
            'paymentTerms' => self::PAYMENT_TERMS,
            'states' => self::STATES,
        ]);
    }

    public function store(StoreServiceVendorRequest $request): RedirectResponse
    {
        $society = Society::first();
        $data = $request->validated();

        $vendor = ServiceVendor::create($data + [
            'society_id' => $society?->id,
            'vendor_code' => $data['vendor_code'] ?? $this->nextVendorCode(),
            'approval_status' => $data['approval_status'] ?? 'pending',
        ]);

        return redirect()->route('society.vendors.index')
            ->with('success', "Vendor {$vendor->name} added successfully.");
    }

    public function edit(ServiceVendor $vendor): View
    {
        return view('society.vendors.edit', [
            'active' => 'vendors',
            'vendor' => $vendor,
            'categories' => self::CATEGORIES,
            'paymentTerms' => self::PAYMENT_TERMS,
            'states' => self::STATES,
        ]);
    }

    public function update(StoreServiceVendorRequest $request, ServiceVendor $vendor): RedirectResponse
    {
        $data = $request->validated();
        $data['vendor_code'] = ($data['vendor_code'] ?? null) ?: $vendor->vendor_code;
        $data['approval_status'] = $data['approval_status'] ?? $vendor->approval_status;

        $vendor->update($data);

        return redirect()->route('society.vendors.index')
            ->with('success', "Vendor {$vendor->name} updated successfully.");
    }

    public function destroy(ServiceVendor $vendor): RedirectResponse
    {
        $vendor->delete();

        return redirect()->route('society.vendors.index')
            ->with('success', 'Vendor removed successfully.');
    }

    private function nextVendorCode(): string
    {
        $next = (ServiceVendor::max('id') ?? 0) + 1;

        return 'VND-'.str_pad((string) $next, 4, '0', STR_PAD_LEFT);
    }

    /**
     * Demo stat-card figures matching the Vendor Management screenshot.
     *
     * @return array<string, string>
     */
    private function vendorStats(): array
    {
        return [
            'total' => '48',
            'active' => '36',
            'pending' => '5',
            'inactive' => '7',
        ];
    }
}
