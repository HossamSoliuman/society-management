<?php

namespace App\Http\Controllers\Society;

use App\Http\Controllers\Controller;
use App\Imports\MaintenanceBillImport;
use App\Jobs\SendMaintenanceBill;
use App\Models\Account;
use App\Models\ChargeHead;
use App\Models\MaintenanceBill;
use App\Models\Member;
use App\Models\PaymentMode;
use App\Models\Society;
use App\Models\Unit;
use App\Services\BillDocumentService;
use App\Services\BillingService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;
use InvalidArgumentException;
use Maatwebsite\Excel\Facades\Excel;

class BillController extends Controller
{
    private const IMPORT_SESSION_KEY = 'billing.bulk-import';

    public function __construct(
        private readonly BillingService $billing,
        private readonly BillDocumentService $documents,
    ) {}

    public function index(Request $request): View
    {
        $society = $this->currentSociety();

        $query = MaintenanceBill::query()
            ->forSociety($society)
            ->when($request->filled('q'), function ($q) use ($request) {
                $term = $request->string('q');
                $q->where(function ($sub) use ($term) {
                    $sub->where('bill_number', 'like', "%{$term}%")
                        ->orWhere('member_name', 'like', "%{$term}%")
                        ->orWhere('flat_number', 'like', "%{$term}%")
                        ->orWhere('tower_wing', 'like', "%{$term}%");
                });
            })
            ->when($request->filled('month'), fn ($q) => $q->where('bill_month', $request->string('month')))
            ->when($request->filled('cycle'), fn ($q) => $q->where('bill_cycle', $request->string('cycle')))
            ->when($request->filled('status'), fn ($q) => $q->where('status', $request->string('status')))
            ->when($request->filled('tower'), fn ($q) => $q->where('tower_wing', $request->string('tower')))
            ->orderByDesc('bill_date')
            ->orderByDesc('id');

        $perPage = (int) $request->input('per_page', 10);
        $bills = $query->paginate($perPage)->withQueryString();

        $base = MaintenanceBill::query()->forSociety($society);

        $months = (clone $base)->select('bill_month')->distinct()->pluck('bill_month');
        $cycles = (clone $base)->select('bill_cycle')->distinct()->pluck('bill_cycle');
        $towers = (clone $base)->select('tower_wing')->distinct()->orderBy('tower_wing')->pluck('tower_wing');

        return view('society.billing.bills.index', [
            'society' => $society,
            'bills' => $bills,
            'kpis' => $this->kpis($society, $request->string('month')->toString() ?: null),
            'months' => $months,
            'cycles' => $cycles,
            'towers' => $towers,
        ]);
    }

    public function create(): View
    {
        $society = $this->currentSociety();

        $chargeHeads = $this->activeChargeHeads($society);

        $members = Member::query()->forSociety($society)->orderBy('name')->get();
        $units = Unit::query()->forSociety($society)->orderBy('unit_number')->get();

        $towers = $units->pluck('building')->filter()->unique()->values();
        $floors = $units->pluck('floor')->filter()->unique()->values();

        $paymentModes = PaymentMode::where('status', 'active')->orderBy('name')->pluck('name');
        $collectionAccounts = $this->collectionAccounts($society);
        $settings = $this->billing->settings($society);

        // Pre-filled lines come from the society's recurring charge heads.
        $defaultLines = $chargeHeads
            ->where('type', 'recurring')
            ->take(4)
            ->map(fn (ChargeHead $head) => [
                'name' => $head->name,
                'description' => $head->description,
                'amount' => (float) $head->default_amount,
            ])
            ->values()
            ->all();

        return view('society.billing.bills.create', compact(
            'society', 'chargeHeads', 'members', 'units', 'towers', 'floors',
            'paymentModes', 'collectionAccounts', 'defaultLines', 'settings',
        ));
    }

    public function store(Request $request): RedirectResponse
    {
        $society = $this->currentSociety();

        $data = $request->validate([
            'bill_month' => ['required', 'string', 'max:255'],
            'bill_date' => ['required', 'string', 'max:255'],
            'due_date' => ['required', 'string', 'max:255'],
            'billing_type' => ['required', 'string', 'max:255'],
            'bill_cycle' => ['nullable', 'string', 'max:255'],
            'notes' => ['nullable', 'string'],
            'member_id' => ['nullable', 'integer', Rule::exists('members', 'id')->where('society_id', $society->id)],
            'unit_id' => ['nullable', 'integer', Rule::exists('units', 'id')->where('society_id', $society->id)],
            'member_name' => ['nullable', 'string', 'max:255'],
            'flat_number' => ['nullable', 'string', 'max:255'],
            'tower_wing' => ['nullable', 'string', 'max:255'],
            'floor' => ['nullable', 'string', 'max:255'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.charge_head_name' => ['required', 'string', 'max:255'],
            'items.*.charge_head_id' => ['nullable', 'integer'],
            'items.*.description' => ['nullable', 'string', 'max:255'],
            'items.*.amount' => ['required', 'numeric', 'min:0'],
            'discount' => ['nullable', 'numeric', 'min:0'],
            'late_fee' => ['nullable', 'numeric', 'min:0'],
            'collection_account' => ['required', 'string', 'max:255'],
            'payment_mode' => ['nullable', 'string', 'max:255'],
            'reference_no' => ['nullable', 'string', 'max:255'],
            'send_email' => ['nullable', 'boolean'],
            'send_sms' => ['nullable', 'boolean'],
            'send_whatsapp' => ['nullable', 'boolean'],
        ]);

        $member = isset($data['member_id']) ? Member::find($data['member_id']) : null;
        $unit = isset($data['unit_id']) ? Unit::find($data['unit_id']) : null;

        $bill = $this->billing->createBill($society, [
            'member' => $member,
            'unit' => $unit,
            'member_name' => $data['member_name'] ?? null,
            'flat_number' => $data['flat_number'] ?? null,
            'tower_wing' => $data['tower_wing'] ?? null,
            'floor' => $data['floor'] ?? null,
            'bill_month' => $data['bill_month'],
            'bill_date' => Carbon::parse($data['bill_date']),
            'due_date' => Carbon::parse($data['due_date']),
            'bill_cycle' => $data['bill_cycle'] ?? $data['bill_month'],
            'billing_type' => $data['billing_type'],
            'lines' => array_values($data['items']),
            'discount' => (float) ($data['discount'] ?? 0),
            'late_fee' => (float) ($data['late_fee'] ?? 0),
            'collection_account' => $data['collection_account'],
            'payment_mode' => $data['payment_mode'] ?? null,
            'reference_no' => $data['reference_no'] ?? null,
            'notes' => $data['notes'] ?? null,
            'send_email' => $request->boolean('send_email'),
            'send_sms' => $request->boolean('send_sms'),
            'send_whatsapp' => $request->boolean('send_whatsapp'),
        ]);

        if ($bill->send_email || $bill->send_sms || $bill->send_whatsapp) {
            SendMaintenanceBill::dispatch($bill);
        }

        return redirect()->route('society.billing.bills.index')
            ->with('success', "Bill {$bill->bill_number} generated successfully.");
    }

    public function generate(Request $request): View
    {
        $society = $this->currentSociety();
        $settings = $this->billing->settings($society);
        $units = Unit::query()->forSociety($society)->where('status', 'occupied')->orderBy('building')->orderBy('unit_number')->get();

        $billDate = Carbon::today();

        return view('society.billing.bills.generate', [
            'society' => $society,
            'chargeHeads' => $this->activeChargeHeads($society),
            'units' => $units,
            'towers' => $units->pluck('building')->filter()->unique()->values(),
            'settings' => $settings,
            'defaultMonth' => $billDate->format('F Y'),
            'defaultBillDate' => $billDate->toDateString(),
            'defaultDueDate' => $billDate->copy()->addDays((int) ($settings->due_date_days ?: 15))->toDateString(),
            'preview' => null,
        ]);
    }

    /**
     * Preview (dry run) or confirm bulk generation, depending on the `confirm` flag.
     */
    public function storeGenerate(Request $request): View|RedirectResponse
    {
        $society = $this->currentSociety();

        $data = $request->validate([
            'bill_month' => ['required', 'string', 'max:50'],
            'bill_cycle' => ['nullable', 'string', 'max:50'],
            'billing_type' => ['nullable', 'string', 'max:100'],
            'bill_date' => ['required', 'date'],
            'due_date' => ['required', 'date', 'after_or_equal:bill_date'],
            'charge_heads' => ['required', 'array', 'min:1'],
            'charge_heads.*' => ['integer', Rule::exists('charge_heads', 'id')->where('society_id', $society->id)],
            'tower' => ['nullable', 'string', 'max:100'],
            'send_email' => ['nullable', 'boolean'],
            'send_sms' => ['nullable', 'boolean'],
            'notes' => ['nullable', 'string', 'max:500'],
            'confirm' => ['nullable', 'boolean'],
        ]);

        $options = [
            'bill_date' => $data['bill_date'],
            'due_date' => $data['due_date'],
            'bill_cycle' => $data['bill_cycle'] ?? $data['bill_month'],
            'billing_type' => $data['billing_type'] ?? null,
            'send_email' => $request->boolean('send_email'),
            'send_sms' => $request->boolean('send_sms'),
            'notes' => $data['notes'] ?? null,
        ];
        if (! empty($data['tower'])) {
            $options['unit_ids'] = Unit::query()->forSociety($society)->where('building', $data['tower'])->pluck('id')->all();
        }

        if (! $request->boolean('confirm')) {
            $preview = $this->billing->previewForPeriod($society, $data['bill_month'], $data['charge_heads'], $options);
            $units = Unit::query()->forSociety($society)->where('status', 'occupied')->orderBy('building')->orderBy('unit_number')->get();
            $settings = $this->billing->settings($society);

            return view('society.billing.bills.generate', [
                'society' => $society,
                'chargeHeads' => $this->activeChargeHeads($society),
                'units' => $units,
                'towers' => $units->pluck('building')->filter()->unique()->values(),
                'settings' => $settings,
                'defaultMonth' => $data['bill_month'],
                'defaultBillDate' => $data['bill_date'],
                'defaultDueDate' => $data['due_date'],
                'preview' => $preview,
            ]);
        }

        try {
            $bills = $this->billing->generateForPeriod($society, $data['bill_month'], $data['charge_heads'], $options);
        } catch (InvalidArgumentException $exception) {
            throw ValidationException::withMessages(['charge_heads' => $exception->getMessage()]);
        }

        return redirect()->route('society.billing.bills.index', ['month' => $data['bill_month']])
            ->with('success', "{$bills->count()} bill(s) generated for {$data['bill_month']}.");
    }

    public function show(MaintenanceBill $bill): View
    {
        $bill->load(['items', 'payments']);

        return view('society.billing.bills.show', [
            'design' => $this->documents->design($bill->society_id),
            'billModel' => $bill,
            'bill' => $this->documents->templateData($bill),
        ]);
    }

    public function print(MaintenanceBill $bill): View
    {
        $bill->load('items');

        return view('society.billing.bills.print', [
            'design' => $this->documents->design($bill->society_id),
            'bill' => $this->documents->templateData($bill),
        ]);
    }

    public function pdf(MaintenanceBill $bill): Response
    {
        return $this->documents->renderPdf($bill)->download($this->documents->fileName($bill));
    }

    public function send(MaintenanceBill $bill): RedirectResponse
    {
        $bill->forceFill(['send_email' => true])->saveQuietly();
        SendMaintenanceBill::dispatch($bill);

        return back()->with('success', "Bill {$bill->bill_number} queued for delivery.");
    }

    public function bulkUpload(Request $request): View
    {
        $society = $this->currentSociety();
        $import = $request->session()->get(self::IMPORT_SESSION_KEY);

        return view('society.billing.bills.bulk-upload', [
            'society' => $society,
            'preview' => $import && ($import['society_id'] ?? null) === $society->id ? $import : null,
        ]);
    }

    /**
     * Parse the spreadsheet, validate every row and stash the result for review.
     */
    public function bulkStore(Request $request): RedirectResponse
    {
        $society = $this->currentSociety();

        $request->validate([
            'file' => ['required', 'file', 'mimes:xlsx,xls,csv,txt', 'max:5120'],
        ]);

        $import = new MaintenanceBillImport($society);
        Excel::import($import, $request->file('file'));

        $request->session()->put(self::IMPORT_SESSION_KEY, [
            'society_id' => $society->id,
            'file_name' => $request->file('file')->getClientOriginalName(),
            'rows' => $import->rows,
            'errors' => $import->errors,
        ]);

        $valid = count($import->rows);
        $invalid = count($import->errors);

        return redirect()->route('society.billing.bills.bulk')
            ->with($invalid > 0 ? 'error' : 'success', "{$valid} valid row(s) parsed".($invalid > 0 ? ", {$invalid} row(s) need attention." : '. Review and confirm to generate bills.'));
    }

    public function bulkConfirm(Request $request): RedirectResponse
    {
        $society = $this->currentSociety();
        $import = $request->session()->get(self::IMPORT_SESSION_KEY);

        abort_unless($import && ($import['society_id'] ?? null) === $society->id, 404);

        if (empty($import['rows'])) {
            return redirect()->route('society.billing.bills.bulk')->with('error', 'No valid rows to import.');
        }

        $bills = $this->billing->createFromRows($society, $import['rows']);
        $request->session()->forget(self::IMPORT_SESSION_KEY);

        return redirect()->route('society.billing.bills.index')
            ->with('success', "{$bills->count()} bill(s) created from {$import['file_name']}.");
    }

    public function bulkDiscard(Request $request): RedirectResponse
    {
        $request->session()->forget(self::IMPORT_SESSION_KEY);

        return redirect()->route('society.billing.bills.bulk')->with('success', 'Upload discarded.');
    }

    /**
     * Aggregates for the stat cards (optionally for one bill month).
     *
     * @return array<string, int|float>
     */
    private function kpis(Society $society, ?string $month): array
    {
        $rows = MaintenanceBill::query()
            ->forSociety($society)
            ->when($month, fn ($q) => $q->where('bill_month', $month))
            ->selectRaw('status, COUNT(*) as c, COALESCE(SUM(total_amount),0) as total, COALESCE(SUM(collected_amount),0) as collected, COALESCE(SUM(outstanding_amount),0) as outstanding')
            ->groupBy('status')
            ->get()
            ->keyBy('status');

        $count = fn (array $statuses) => (int) collect($statuses)->sum(fn ($s) => $rows[$s]->c ?? 0);
        $sum = fn (array $statuses, string $col) => (float) collect($statuses)->sum(fn ($s) => $rows[$s]->{$col} ?? 0);

        return [
            'total_bills' => (int) $rows->sum('c'),
            'paid_bills' => $count(['paid']),
            'paid_amount' => (float) $rows->sum('collected'),
            'pending_bills' => $count(['pending', 'partial']),
            'pending_amount' => $sum(['pending', 'partial'], 'outstanding'),
            'overdue_bills' => $count(['overdue']),
            'overdue_amount' => $sum(['overdue'], 'outstanding'),
            'total_amount' => (float) $rows->sum('total'),
        ];
    }

    /**
     * @return Collection<int, ChargeHead>
     */
    private function activeChargeHeads(Society $society): Collection
    {
        return ChargeHead::query()
            ->forSociety($society)
            ->where('status', 'active')
            ->orderBy('sort_order')
            ->get();
    }

    /**
     * Bank / cash accounts a payment can be collected into: detail accounts
     * from the chart of accounts, else the society's bank + cash.
     *
     * @return array<int, string>
     */
    private function collectionAccounts(Society $society): array
    {
        $accounts = Account::query()
            ->forSociety($society)
            ->where('type', 'detail')
            ->where('status', 'active')
            ->where(fn ($q) => $q->where('name', 'like', '%Bank%')->orWhere('name', 'like', '%Cash%'))
            ->orderBy('code')
            ->pluck('name')
            ->all();

        if ($accounts !== []) {
            return $accounts;
        }

        return array_values(array_filter([
            $society->bank_name ? trim($society->bank_name.' - A/c '.($society->account_number ?? '')) : null,
            'Cash in Hand',
        ]));
    }
}
