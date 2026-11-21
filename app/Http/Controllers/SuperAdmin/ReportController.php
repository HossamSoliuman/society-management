<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Exports\ArrayReportExport;
use App\Http\Controllers\Controller;
use App\Models\Payment;
use App\Models\Society;
use App\Models\SocietyType;
use App\Models\SubscriptionPlan;
use App\Services\PlatformReportService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\View\View;
use Maatwebsite\Excel\Facades\Excel;
use Symfony\Component\HttpFoundation\Response;

/**
 * Platform-wide reports with date filters and Excel / PDF export of the
 * currently filtered rows.
 */
class ReportController extends Controller
{
    public function __construct(private readonly PlatformReportService $reports) {}

    public function index(): View
    {
        return view('superadmin.report.index');
    }

    public function societyReport(Request $request): View|Response
    {
        return $this->render($request, 'society');
    }

    public function revenueReport(Request $request): View|Response
    {
        return $this->render($request, 'revenue');
    }

    public function subscriptionReport(Request $request): View|Response
    {
        return $this->render($request, 'subscription');
    }

    public function paymentReport(Request $request): View|Response
    {
        return $this->render($request, 'payment');
    }

    private function render(Request $request, string $report): View|Response
    {
        $validated = $request->validate([
            'from' => ['nullable', 'date'],
            'to' => ['nullable', 'date', 'after_or_equal:from'],
            'export' => ['nullable', 'in:xlsx,pdf'],
        ]);

        $from = isset($validated['from']) ? Carbon::parse($validated['from']) : null;
        $to = isset($validated['to']) ? Carbon::parse($validated['to']) : null;
        $data = $this->reports->build($report, $from, $to, $request->only(['status', 'type', 'plan', 'method', 'society', 'q']));

        if (($validated['export'] ?? null) === 'xlsx') {
            return Excel::download(
                new ArrayReportExport($data['headings'], $data['rows'], $data['title']),
                $this->exportFileName($report.'-report'),
            );
        }

        if (($validated['export'] ?? null) === 'pdf') {
            return Pdf::loadView('superadmin.report.pdf', ['report' => $data])
                ->setPaper('a4', 'landscape')
                ->download($this->exportFileName($report.'-report', 'pdf'));
        }

        return view('superadmin.report.show', [
            'report' => $report,
            'data' => $data,
            'societyTypes' => $report === 'society' ? SocietyType::query()->orderBy('name')->get(['id', 'name']) : collect(),
            'plans' => $report === 'subscription' ? SubscriptionPlan::query()->orderBy('name')->get(['id', 'name']) : collect(),
            'societies' => in_array($report, ['revenue', 'payment'], true) ? Society::query()->orderBy('name')->get(['id', 'name']) : collect(),
            'methods' => in_array($report, ['revenue', 'payment'], true)
                ? Payment::query()->whereNotNull('payment_method')->distinct()->orderBy('payment_method')->pluck('payment_method')
                : collect(),
        ]);
    }
}
