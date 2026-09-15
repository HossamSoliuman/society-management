<?php

namespace App\Http\Controllers\Society;

use App\Exports\ArrayReportExport;
use App\Http\Controllers\Controller;
use App\Models\ExpenseCategory;
use App\Models\MaintenanceBill;
use App\Services\SocietyReportService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\View\View;
use Maatwebsite\Excel\Facades\Excel;
use Symfony\Component\HttpFoundation\Response;

/**
 * Society-level reports (collections, expenses, defaulters) with date filters
 * and Excel / PDF export of the currently filtered rows.
 */
class ReportController extends Controller
{
    private const REPORTS = ['collection', 'expense', 'defaulter'];

    public function __construct(private readonly SocietyReportService $reports) {}

    public function show(Request $request, string $report): View|Response
    {
        abort_unless(in_array($report, self::REPORTS, true), 404);
        $society = $this->currentSociety();

        [$from, $to] = $this->period($request);
        $data = match ($report) {
            'collection' => $this->reports->collection($society, $from, $to, $request->only(['mode', 'status'])),
            'expense' => $this->reports->expense($society, $from, $to, $request->only(['category', 'status'])),
            default => $this->reports->defaulters($society, $to, $request->only(['tower', 'min_days'])),
        };

        if ($request->string('export')->toString() === 'xlsx') {
            return Excel::download(
                new ArrayReportExport($data['headings'], $data['rows'], $data['title']),
                $this->fileName($society->prefix, $report, 'xlsx'),
            );
        }

        if ($request->string('export')->toString() === 'pdf') {
            return Pdf::loadView('society.reports.pdf', ['society' => $society, 'report' => $data])
                ->setPaper('a4', 'landscape')
                ->download($this->fileName($society->prefix, $report, 'pdf'));
        }

        return view('society.reports.show', [
            'society' => $society,
            'report' => $report,
            'data' => $data,
            'from' => $from->toDateString(),
            'to' => $to->toDateString(),
            'categories' => ExpenseCategory::query()->forSociety($society)->orderBy('name')->get(['id', 'name']),
            'towers' => MaintenanceBill::query()->forSociety($society)->whereNotNull('tower_wing')->distinct()->orderBy('tower_wing')->pluck('tower_wing'),
        ]);
    }

    /**
     * @return array{0: Carbon, 1: Carbon}
     */
    private function period(Request $request): array
    {
        $to = $request->filled('to') ? Carbon::parse($request->string('to')) : Carbon::today();
        $from = $request->filled('from') ? Carbon::parse($request->string('from')) : $to->copy()->startOfMonth();

        return [$from, $to];
    }

    private function fileName(string $prefix, string $report, string $extension): string
    {
        return strtolower($prefix.'-'.$report.'-report-'.now()->format('Ymd')).'.'.$extension;
    }
}
