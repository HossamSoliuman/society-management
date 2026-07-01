<?php

namespace App\Http\Controllers\Society;

use App\Http\Controllers\Controller;
use App\Models\ChargeHead;
use App\Models\Society;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ChargeHeadController extends Controller
{
    public function index(Request $request): View
    {
        $society = Society::first();

        $query = ChargeHead::query()
            ->when($society, fn ($q) => $q->where('society_id', $society->id))
            ->when($request->filled('q'), function ($q) use ($request) {
                $term = $request->string('q');
                $q->where(fn ($sub) => $sub->where('name', 'like', "%{$term}%")
                    ->orWhere('description', 'like', "%{$term}%"));
            })
            ->when($request->filled('category'), fn ($q) => $q->where('category', $request->string('category')))
            ->when($request->filled('status'), fn ($q) => $q->where('status', $request->string('status')))
            ->orderBy('sort_order')
            ->orderBy('id');

        $perPage = (int) $request->input('per_page', 10);
        $chargeHeads = $query->paginate($perPage)->withQueryString();

        $base = ChargeHead::query()->when($society, fn ($q) => $q->where('society_id', $society->id));

        $stats = [
            'total' => (clone $base)->count(),
            'active' => (clone $base)->where('status', 'active')->count(),
            'inactive' => (clone $base)->where('status', 'inactive')->count(),
            'used' => (clone $base)->where('status', 'active')->where('applies_to', 'All Bills')->count(),
        ];

        return view('society.billing.settings.charge-heads', compact('society', 'chargeHeads', 'stats'));
    }

    public function store(Request $request): RedirectResponse
    {
        $society = Society::first();

        $data = $this->validated($request);
        $data['society_id'] = $society?->id;

        ChargeHead::create($data);

        return redirect()->route('society.billing.settings.charge-heads')
            ->with('success', 'Charge head created successfully.');
    }

    public function update(Request $request, ChargeHead $chargeHead): RedirectResponse
    {
        $chargeHead->update($this->validated($request));

        return redirect()->route('society.billing.settings.charge-heads')
            ->with('success', 'Charge head updated successfully.');
    }

    public function destroy(ChargeHead $chargeHead): RedirectResponse
    {
        $chargeHead->delete();

        return redirect()->route('society.billing.settings.charge-heads')
            ->with('success', 'Charge head deleted successfully.');
    }

    /**
     * @return array<string, mixed>
     */
    private function validated(Request $request): array
    {
        return $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:255'],
            'category' => ['required', 'in:maintenance,utilities,parking,amenities,other'],
            'type' => ['required', 'in:recurring,one_time'],
            'calculation_type' => ['required', 'in:per_flat,per_sqft,per_slot,fixed'],
            'default_amount' => ['required', 'numeric', 'min:0'],
            'applies_to' => ['nullable', 'string', 'max:255'],
            'status' => ['required', 'in:active,inactive'],
        ]);
    }
}
