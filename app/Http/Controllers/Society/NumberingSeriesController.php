<?php

namespace App\Http\Controllers\Society;

use App\Http\Controllers\Controller;
use App\Models\CollectionPayment;
use App\Models\MaintenanceBill;
use App\Models\NumberingSeries;
use App\Services\AccountingService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\View\View;

class NumberingSeriesController extends Controller
{
    public function __construct(private readonly AccountingService $accounting) {}

    public function index(): View
    {
        $society = $this->currentSociety();

        $series = NumberingSeries::query()
            ->when($society, fn ($q) => $q->where('society_id', $society->id))
            ->orderByDesc('is_default')
            ->orderBy('id')
            ->get();

        $default = $series->firstWhere('is_default', true) ?? $series->first();
        $fyStart = $this->accounting->financialYearStart(Carbon::today(), $society);
        $monthStart = Carbon::today()->startOfMonth();

        $stats = [
            'active_series' => $series->where('status', 'active')->count(),
            'issued_this_month' => MaintenanceBill::query()->forSociety($society)->where('created_at', '>=', $monthStart)->count()
                + CollectionPayment::query()->forSociety($society)->where('created_at', '>=', $monthStart)->count(),
            'last_generated' => $default?->lastGeneratedNumber(),
            'last_generated_date' => $default?->updated_at?->format('d M Y') ?? '—',
            'financial_year' => $this->accounting->financialYearLabel(Carbon::today(), $society),
            'fy_range' => $fyStart->format('d M Y').' to '.$fyStart->copy()->addYear()->subDay()->format('d M Y'),
            'fy_start' => $fyStart->toDateString(),
        ];

        return view('society.billing.settings.numbering', compact('society', 'series', 'stats'));
    }

    public function store(Request $request): RedirectResponse
    {
        $society = $this->currentSociety();

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
