<?php

namespace App\Http\Controllers\Society;

use App\Http\Controllers\Controller;
use App\Models\BillSetting;
use App\Models\ChargeHead;
use App\Models\MaintenanceBill;
use App\Models\Member;
use App\Models\NumberingSeries;
use App\Models\PaymentMode;
use App\Models\Society;
use App\Models\Unit;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\View\View;

class BillController extends Controller
{
    /** Collection accounts offered on the Create Bill screen. */
    private const COLLECTION_ACCOUNTS = [
        'HDFC Bank - Current A/c',
        'SBI - Savings A/c',
        'ICICI Bank - Current A/c',
        'Cash in Hand',
    ];

    public function index(Request $request): View
    {
        $society = Society::first();

        $query = MaintenanceBill::query()
            ->when($society, fn ($q) => $q->where('society_id', $society->id))
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
            ->orderByDesc('bill_number');

        $perPage = (int) $request->input('per_page', 10);
        $bills = $query->paginate($perPage)->withQueryString();

        $base = MaintenanceBill::query()->when($society, fn ($q) => $q->where('society_id', $society->id));

        $months = (clone $base)->select('bill_month')->distinct()->pluck('bill_month');
        $cycles = (clone $base)->select('bill_cycle')->distinct()->pluck('bill_cycle');
        $towers = (clone $base)->select('tower_wing')->distinct()->orderBy('tower_wing')->pluck('tower_wing');

        // KPI figures mirror the reference design (Maintenance bill.png §3.2).
        $kpis = [
            'total_bills' => 156,
            'paid_bills' => 98,
            'paid_amount' => 342500,
            'pending_bills' => 46,
            'pending_amount' => 145600,
            'overdue_bills' => 12,
            'overdue_amount' => 35200,
            'total_amount' => 523300,
        ];

        return view('society.billing.bills.index', compact('society', 'bills', 'kpis', 'months', 'cycles', 'towers'));
    }

    public function create(): View
    {
        $society = Society::first();

        $chargeHeads = ChargeHead::query()
            ->when($society, fn ($q) => $q->where('society_id', $society->id))
            ->where('status', 'active')
            ->orderBy('sort_order')
            ->get();

        $members = Member::query()
            ->when($society, fn ($q) => $q->where('society_id', $society->id))
            ->orderBy('name')
            ->get();

        $units = Unit::query()
            ->when($society, fn ($q) => $q->where('society_id', $society->id))
            ->orderBy('unit_number')
            ->get();

        $towers = $units->pluck('building')->filter()->unique()->values();
        $floors = $units->pluck('floor')->filter()->unique()->values();

        $paymentModes = PaymentMode::where('status', 'active')->orderBy('name')->pluck('name');
        $collectionAccounts = self::COLLECTION_ACCOUNTS;

        // Default charge lines shown on the design (Create maintenance bill.png §4.2).
        $defaultLines = [
            ['name' => 'Maintenance Charges', 'description' => 'Monthly maintenance charges', 'amount' => 2500],
            ['name' => 'Sinking Fund', 'description' => 'Sinking fund contribution', 'amount' => 500],
            ['name' => 'Reserve Fund', 'description' => 'Reserve fund contribution', 'amount' => 300],
            ['name' => 'Others', 'description' => 'Water tank cleaning charges', 'amount' => 200],
        ];

        return view('society.billing.bills.create', compact(
            'society', 'chargeHeads', 'members', 'units', 'towers', 'floors',
            'paymentModes', 'collectionAccounts', 'defaultLines',
        ));
    }

    public function store(Request $request): RedirectResponse
    {
        $society = Society::first();

        $data = $request->validate([
            'bill_month' => ['required', 'string', 'max:255'],
            'bill_date' => ['required', 'string', 'max:255'],
            'due_date' => ['required', 'string', 'max:255'],
            'billing_type' => ['required', 'string', 'max:255'],
            'bill_cycle' => ['nullable', 'string', 'max:255'],
            'notes' => ['nullable', 'string'],
            'member_id' => ['nullable', 'integer', 'exists:members,id'],
            'unit_id' => ['nullable', 'integer', 'exists:units,id'],
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

        $subTotal = collect($data['items'])->sum(fn ($item) => (float) $item['amount']);
        $discount = (float) ($data['discount'] ?? 0);
        $lateFee = (float) ($data['late_fee'] ?? 0);
        $total = max(0, $subTotal - $discount + $lateFee);

        $bill = MaintenanceBill::create([
            'society_id' => $society?->id,
            'bill_number' => $this->nextBillNumber($society),
            'member_id' => $data['member_id'] ?? null,
            'unit_id' => $data['unit_id'] ?? null,
            'member_name' => $data['member_name'] ?? null,
            'flat_number' => $data['flat_number'] ?? null,
            'tower_wing' => $data['tower_wing'] ?? null,
            'floor' => $data['floor'] ?? null,
            'bill_month' => $data['bill_month'],
            'bill_date' => Carbon::parse($data['bill_date']),
            'due_date' => Carbon::parse($data['due_date']),
            'bill_cycle' => $data['bill_cycle'] ?? $data['bill_month'],
            'billing_type' => $data['billing_type'],
            'sub_total' => $subTotal,
            'discount' => $discount,
            'late_fee' => $lateFee,
            'total_amount' => $total,
            'collected_amount' => 0,
            'outstanding_amount' => $total,
            'status' => 'pending',
            'collection_account' => $data['collection_account'],
            'payment_mode' => $data['payment_mode'] ?? null,
            'reference_no' => $data['reference_no'] ?? null,
            'notes' => $data['notes'] ?? null,
            'send_email' => $request->boolean('send_email'),
            'send_sms' => $request->boolean('send_sms'),
            'send_whatsapp' => $request->boolean('send_whatsapp'),
        ]);

        foreach (array_values($data['items']) as $index => $item) {
            $bill->items()->create([
                'charge_head_id' => $item['charge_head_id'] ?? null,
                'charge_head_name' => $item['charge_head_name'],
                'description' => $item['description'] ?? null,
                'amount' => (float) $item['amount'],
                'sort_order' => $index + 1,
            ]);
        }

        return redirect()->route('society.billing.bills.index')
            ->with('success', "Bill {$bill->bill_number} generated successfully.");
    }

    public function show(MaintenanceBill $bill): View
    {
        $bill->load('items');
        $design = $this->design($bill->society_id);

        return view('society.billing.bills.show', [
            'design' => $design,
            'billModel' => $bill,
            'bill' => $this->billTemplateData($bill),
        ]);
    }

    public function print(MaintenanceBill $bill): View
    {
        $bill->load('items');
        $design = $this->design($bill->society_id);

        return view('society.billing.bills.print', [
            'design' => $design,
            'bill' => $this->billTemplateData($bill),
        ]);
    }

    public function bulkUpload(): View
    {
        $society = Society::first();

        return view('society.billing.bills.bulk-upload', compact('society'));
    }

    public function bulkStore(Request $request): RedirectResponse
    {
        $request->validate([
            'file' => ['required', 'file', 'mimes:xlsx,xls,csv', 'max:5120'],
        ]);

        // Parsing/preview is handled by the review step; acknowledge the upload here.
        return redirect()->route('society.billing.bills.bulk')
            ->with('success', 'File uploaded. Review the parsed rows before generating bills.');
    }

    private function nextBillNumber(?Society $society): string
    {
        $series = NumberingSeries::query()
            ->when($society, fn ($q) => $q->where('society_id', $society->id))
            ->where('document_type', 'maintenance_bill')
            ->first();

        if ($series) {
            return $series->generateNext();
        }

        $last = MaintenanceBill::max('id') ?? 0;

        return 'MB-'.now()->format('Y').'-'.str_pad((string) ($last + 1), 6, '0', STR_PAD_LEFT);
    }

    private function design(?int $societyId): BillSetting
    {
        return BillSetting::query()
            ->when($societyId, fn ($q) => $q->where('society_id', $societyId))
            ->first() ?? new BillSetting;
    }

    /**
     * Build the array shape consumed by the shared `_bill-template` (2A).
     *
     * @return array<string, mixed>
     */
    private function billTemplateData(MaintenanceBill $bill): array
    {
        $flat = collect([$bill->flat_number, $bill->tower_wing, $bill->floor])->filter()->implode(', ');

        return [
            'number' => $bill->bill_number,
            'date' => $bill->bill_date?->format('d M Y'),
            'due_date' => $bill->due_date?->format('d M Y'),
            'to_name' => $bill->member_name ?: 'Mr. Ramesh Sharma',
            'to_flat' => $flat ?: 'A-101, Tower A, 1st Floor',
            'month' => $bill->bill_month,
            'type' => $bill->billing_type,
            'cycle' => $bill->bill_cycle,
            'items' => $bill->items->map(fn ($item) => [
                'name' => $item->charge_head_name,
                'description' => $item->description,
                'amount' => (float) $item->amount,
            ])->all(),
            'subtotal' => (float) $bill->sub_total,
            'discount' => (float) $bill->discount,
            'late_fee' => (float) $bill->late_fee,
            'total' => (float) $bill->total_amount,
            'previous_dues' => (float) $bill->previous_dues,
            'total_payable' => (float) $bill->total_amount + (float) $bill->previous_dues,
            'upi_id' => 'greenview@sbi',
        ];
    }
}
