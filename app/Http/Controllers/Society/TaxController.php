<?php

namespace App\Http\Controllers\Society;

use App\Http\Controllers\Controller;
use App\Models\ChargeHead;
use App\Models\Society;
use App\Models\Tax;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class TaxController extends Controller
{
    public function index(): View
    {
        $society = Society::first();

        $taxes = Tax::query()
            ->when($society, fn ($q) => $q->where('society_id', $society->id))
            ->orderBy('sort_order')
            ->orderBy('id')
            ->get();

        $activeTaxes = $taxes->where('status', 'active');

        $stats = [
            'total' => $taxes->count(),
            'active' => $activeTaxes->count(),
            'total_rate' => $activeTaxes->where('tax_type', 'percentage')->sum('rate'),
            'last_updated' => $taxes->max('updated_at'),
        ];

        $chargeHeads = ChargeHead::query()
            ->when($society, fn ($q) => $q->where('society_id', $society->id))
            ->where('status', 'active')
            ->orderBy('sort_order')
            ->get();

        return view('society.billing.settings.taxes', compact('society', 'taxes', 'stats', 'chargeHeads'));
    }

    public function store(Request $request): RedirectResponse
    {
        $society = Society::first();

        $data = $this->validated($request);
        $data['society_id'] = $society?->id;

        Tax::create($data);

        return redirect()->route('society.billing.settings.taxes')
            ->with('success', 'Tax created successfully.');
    }

    public function update(Request $request, Tax $tax): RedirectResponse
    {
        $tax->update($this->validated($request));

        return redirect()->route('society.billing.settings.taxes')
            ->with('success', 'Tax updated successfully.');
    }

    public function destroy(Tax $tax): RedirectResponse
    {
        $tax->delete();

        return redirect()->route('society.billing.settings.taxes')
            ->with('success', 'Tax deleted successfully.');
    }

    /**
     * @return array<string, mixed>
     */
    private function validated(Request $request): array
    {
        return $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'tax_type' => ['required', 'in:percentage,fixed'],
            'rate' => ['required', 'numeric', 'min:0'],
            'apply_on' => ['nullable', 'string', 'max:255'],
            'slab_from' => ['nullable', 'numeric', 'min:0'],
            'slab_to' => ['nullable', 'numeric', 'min:0'],
            'description' => ['nullable', 'string', 'max:255'],
            'status' => ['required', 'in:active,inactive'],
        ]);
    }
}
