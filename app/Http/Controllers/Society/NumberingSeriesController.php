<?php

namespace App\Http\Controllers\Society;

use App\Http\Controllers\Controller;
use App\Models\NumberingSeries;
use App\Models\Society;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class NumberingSeriesController extends Controller
{
    public function index(): View
    {
        $society = Society::first();

        $series = NumberingSeries::query()
            ->when($society, fn ($q) => $q->where('society_id', $society->id))
            ->orderByDesc('is_default')
            ->orderBy('id')
            ->get();

        $default = $series->firstWhere('is_default', true) ?? $series->first();

        $stats = [
            'active_series' => $series->where('status', 'active')->count(),
            'unused_this_month' => 1245,
            'last_generated' => $default?->lastGeneratedNumber(),
            'last_generated_date' => '30 May 2025',
            'financial_year' => $default?->financial_year ?? '2025-2026',
        ];

        return view('society.billing.settings.numbering', compact('society', 'series', 'stats'));
    }

    public function store(Request $request): RedirectResponse
    {
        $society = Society::first();

        $data = $this->validated($request);
        $data['society_id'] = $society?->id;

        NumberingSeries::create($data);

        return redirect()->route('society.billing.settings.numbering')
            ->with('success', 'Numbering series created successfully.');
    }

    public function update(Request $request, NumberingSeries $series): RedirectResponse
    {
        $series->update($this->validated($request));

        return redirect()->route('society.billing.settings.numbering')
            ->with('success', 'Numbering series updated successfully.');
    }

    public function destroy(NumberingSeries $series): RedirectResponse
    {
        $series->delete();

        return redirect()->route('society.billing.settings.numbering')
            ->with('success', 'Numbering series deleted successfully.');
    }

    /**
     * @return array<string, mixed>
     */
    private function validated(Request $request): array
    {
        return $request->validate([
            'document_type' => ['required', 'in:maintenance_bill,receipt,credit_note,debit_note,refund'],
            'prefix' => ['nullable', 'string', 'max:20'],
            'format' => ['required', 'string', 'max:50'],
            'next_number' => ['required', 'integer', 'min:1'],
            'reset_frequency' => ['required', 'in:never,yearly,monthly,daily'],
            'financial_year' => ['nullable', 'string', 'max:20'],
            'start_date' => ['nullable', 'date'],
            'description' => ['nullable', 'string', 'max:255'],
            'status' => ['required', 'in:active,inactive'],
        ]);
    }
}
