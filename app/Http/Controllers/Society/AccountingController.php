<?php

namespace App\Http\Controllers\Society;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreAccountingPaymentRequest;
use App\Http\Requests\StoreAccountRequest;
use App\Http\Requests\StoreJournalEntryRequest;
use App\Http\Requests\StoreReceiptRequest;
use App\Models\Account;
use App\Models\AccountGroup;
use App\Models\AccountingPayment;
use App\Models\JournalEntry;
use App\Models\Receipt;
use App\Models\Society;
use App\Models\Transaction;
use App\Services\AccountingService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\View\View;

class AccountingController extends Controller
{
    /** Payment modes offered across the accounting create forms and filters. */
    private const PAYMENT_MODES = ['UPI', 'Card', 'Net Banking', 'Cheque', 'Cash'];

    private const LOCATIONS = ['Tower A', 'Tower B', 'Tower C', 'Clubhouse'];

    public function __construct(private readonly AccountingService $accounting) {}

    public function index(): View
    {
        $society = $this->currentSociety();

        $recent = Transaction::query()
            ->with('account')
            ->when($society, fn ($q) => $q->where('society_id', $society->id))
            ->orderByDesc('date')
            ->orderByDesc('id')
            ->limit(5)
            ->get();

        return view('society.accounting.index', [
            'active' => 'dashboard',
            'stats' => $this->dashboardStats(),
            'recent' => $recent,
            'cashFlow' => $this->accounting->cashFlowSeries(),
            'balanceSummary' => $this->accounting->accountBalanceSummary(),
            'bankAccounts' => $this->bankAccounts(),
            'accountsForFilter' => $this->detailAccounts($society),
            'paymentModes' => self::PAYMENT_MODES,
            'locations' => self::LOCATIONS,
        ]);
    }

    public function transactions(Request $request): View
    {
        $society = $this->currentSociety();

        $tab = $request->string('tab')->toString() ?: 'all';
        $tabType = match ($tab) {
            'receipts' => 'receipt',
            'payments' => 'payment',
            'journal-entries' => 'journal',
            default => null,
        };

        $transactions = Transaction::query()
            ->with('account')
            ->when($society, fn ($q) => $q->where('society_id', $society->id))
            ->when($tabType, fn ($q) => $q->where('type', $tabType))
            ->when($request->filled('type'), fn ($q) => $q->where('type', $request->string('type')))
            ->when($request->filled('account'), fn ($q) => $q->where('account_id', $request->integer('account')))
            ->when($request->filled('mode'), fn ($q) => $q->where('payment_mode', $request->string('mode')))
            ->when($request->filled('location'), fn ($q) => $q->where('location', $request->string('location')))
            ->when($request->filled('from'), fn ($q) => $q->whereDate('date', '>=', Carbon::parse($request->string('from'))))
            ->when($request->filled('to'), fn ($q) => $q->whereDate('date', '<=', Carbon::parse($request->string('to'))))
            ->orderByDesc('date')
            ->orderByDesc('id')
            ->paginate(10)
            ->withQueryString();

        return view('society.accounting.transactions', [
            'active' => 'transactions',
            'tab' => $tab,
            'transactions' => $transactions,
            'stats' => $this->transactionStats(),
            'summary' => $this->accounting->transactionSummary(),
            'accountsForFilter' => $this->detailAccounts($society),
            'paymentModes' => self::PAYMENT_MODES,
            'locations' => self::LOCATIONS,
        ]);
    }

    public function receipts(Request $request): View
    {
        $society = $this->currentSociety();

        $receipts = Receipt::query()
            ->with('account')
            ->when($society, fn ($q) => $q->where('society_id', $society->id))
            ->when($request->filled('type'), fn ($q) => $q->where('receipt_type', $request->string('type')))
            ->when($request->filled('account'), fn ($q) => $q->where('account_id', $request->integer('account')))
            ->when($request->filled('location'), fn ($q) => $q->where('location', $request->string('location')))
            ->when($request->filled('q'), function ($q) use ($request) {
                $term = $request->string('q');
                $q->where(function ($sub) use ($term) {
                    $sub->where('receipt_no', 'like', "%{$term}%")
                        ->orWhere('reference_no', 'like', "%{$term}%")
                        ->orWhere('payer_name', 'like', "%{$term}%");
                });
            })
            ->orderByDesc('date')
            ->orderByDesc('id')
            ->paginate(8)
            ->withQueryString();

        return view('society.accounting.receipts', [
            'active' => 'receipts',
            'receipts' => $receipts,
            'stats' => $this->receiptStats(),
            'receiptSummary' => $this->receiptSummaryRail(),
            'paymentModesRail' => $this->paymentModesRail(),
            'accountsForFilter' => $this->detailAccounts($society),
            'receiptTypes' => $this->receiptTypes(),
            'locations' => self::LOCATIONS,
        ]);
    }

    public function createReceipt(): View
    {
        $society = $this->currentSociety();

        return view('society.accounting.receipts-create', [
            'active' => 'receipts',
            'accounts' => $this->detailAccounts($society),
            'receiptTypes' => $this->receiptTypes(),
            'paymentModes' => self::PAYMENT_MODES,
            'locations' => self::LOCATIONS,
        ]);
    }

    public function storeReceipt(StoreReceiptRequest $request): RedirectResponse
    {
        $society = $this->currentSociety();
        $data = $request->validated();

        $receipt = Receipt::create($data + [
            'society_id' => $society?->id,
            'receipt_no' => $this->nextReceiptNo(),
            'status' => 'completed',
        ]);

        return redirect()->route('society.accounting.receipts')
            ->with('success', "Receipt {$receipt->receipt_no} recorded successfully.");
    }

    public function payments(Request $request): View
    {
        $society = $this->currentSociety();

        $payments = AccountingPayment::query()
            ->with('account')
            ->when($society, fn ($q) => $q->where('society_id', $society->id))
            ->when($request->filled('mode'), fn ($q) => $q->where('mode', $request->string('mode')))
            ->when($request->filled('account'), fn ($q) => $q->where('account_id', $request->integer('account')))
            ->when($request->filled('q'), function ($q) use ($request) {
                $term = $request->string('q');
                $q->where(function ($sub) use ($term) {
                    $sub->where('payment_no', 'like', "%{$term}%")
                        ->orWhere('payee', 'like', "%{$term}%")
                        ->orWhere('purpose', 'like', "%{$term}%");
                });
            })
            ->orderByDesc('date')
            ->orderByDesc('id')
            ->paginate(8)
            ->withQueryString();

        return view('society.accounting.payments', [
            'active' => 'payments',
            'payments' => $payments,
            'stats' => $this->paymentStats(),
            'accountsForFilter' => $this->detailAccounts($society),
            'paymentModes' => self::PAYMENT_MODES,
        ]);
    }

    public function createPayment(): View
    {
        $society = $this->currentSociety();

        return view('society.accounting.payments-create', [
            'active' => 'payments',
            'accounts' => $this->detailAccounts($society),
            'paymentModes' => self::PAYMENT_MODES,
        ]);
    }

    public function storePayment(StoreAccountingPaymentRequest $request): RedirectResponse
    {
        $society = $this->currentSociety();
        $data = $request->validated();

        $payment = AccountingPayment::create($data + [
            'society_id' => $society?->id,
            'payment_no' => $this->nextPaymentNo(),
            'status' => 'completed',
        ]);

        return redirect()->route('society.accounting.payments')
            ->with('success', "Payment {$payment->payment_no} recorded successfully.");
    }

    public function journalEntries(Request $request): View
    {
        $society = $this->currentSociety();

        $entries = JournalEntry::query()
            ->withCount('lines')
            ->when($society, fn ($q) => $q->where('society_id', $society->id))
            ->when($request->filled('q'), fn ($q) => $q->where('entry_no', 'like', '%'.$request->string('q').'%')->orWhere('narration', 'like', '%'.$request->string('q').'%'))
            ->orderByDesc('date')
            ->orderByDesc('id')
            ->paginate(10)
            ->withQueryString();

        return view('society.accounting.journal-entries', [
            'active' => 'journal-entries',
            'entries' => $entries,
            'stats' => $this->journalStats(),
        ]);
    }

    public function createJournalEntry(): View
    {
        $society = $this->currentSociety();

        return view('society.accounting.journal-entries-create', [
            'active' => 'journal-entries',
            'accounts' => $this->detailAccounts($society),
        ]);
    }

    public function storeJournalEntry(StoreJournalEntryRequest $request): RedirectResponse
    {
        $society = $this->currentSociety();
        $data = $request->validated();

        $lines = collect($data['lines']);
        $totalDebit = $lines->sum(fn ($line) => (float) ($line['debit'] ?? 0));
        $totalCredit = $lines->sum(fn ($line) => (float) ($line['credit'] ?? 0));

        $entry = JournalEntry::create([
            'society_id' => $society?->id,
            'entry_no' => $this->nextJournalNo(),
            'date' => $data['date'],
            'narration' => $data['narration'] ?? null,
            'total_debit' => $totalDebit,
            'total_credit' => $totalCredit,
            'status' => 'posted',
        ]);

        foreach ($lines as $line) {
            $entry->lines()->create([
                'account_id' => $line['account_id'],
                'debit' => (float) ($line['debit'] ?? 0),
                'credit' => (float) ($line['credit'] ?? 0),
            ]);
        }

        return redirect()->route('society.accounting.journal-entries')
            ->with('success', "Journal entry {$entry->entry_no} posted successfully.");
    }

    public function bankReconciliation(Request $request): View
    {
        $society = $this->currentSociety();

        return view('society.accounting.bank-reconciliation', [
            'active' => 'bank-reconciliation',
            'bankAccounts' => $this->bankAccounts(),
            'rows' => $this->reconciliationRows(),
        ]);
    }

    public function trialBalance(): View
    {
        return view('society.accounting.trial-balance', [
            'active' => 'trial-balance',
            'trialBalance' => $this->accounting->trialBalance(),
        ]);
    }

    public function profitLoss(): View
    {
        return view('society.accounting.profit-loss', [
            'active' => 'profit-loss',
            'pl' => $this->accounting->profitAndLoss(),
            'accountsForFilter' => $this->detailAccounts($this->currentSociety()),
        ]);
    }

    public function balanceSheet(): View
    {
        return view('society.accounting.balance-sheet', [
            'active' => 'balance-sheet',
            'bs' => $this->accounting->balanceSheet(),
        ]);
    }

    public function chartOfAccounts(Request $request): View
    {
        $society = $this->currentSociety();
        $tab = $request->string('tab')->toString() === 'groups' ? 'groups' : 'list';

        $accounts = Account::query()
            ->with('group')
            ->when($society, fn ($q) => $q->where('society_id', $society->id))
            ->when($request->filled('group'), fn ($q) => $q->where('group_id', $request->integer('group')))
            ->when($request->filled('type'), fn ($q) => $q->where('type', $request->string('type')))
            ->when($request->filled('status'), fn ($q) => $q->where('status', $request->string('status')))
            ->when($request->filled('q'), function ($q) use ($request) {
                $term = $request->string('q');
                $q->where(function ($sub) use ($term) {
                    $sub->where('code', 'like', "%{$term}%")
                        ->orWhere('name', 'like', "%{$term}%");
                });
            })
            ->orderBy('display_order')
            ->orderBy('code')
            ->paginate(10)
            ->withQueryString();

        $groups = AccountGroup::query()
            ->when($society, fn ($q) => $q->where('society_id', $society->id))
            ->withCount('accounts')
            ->orderBy('id')
            ->get();

        return view('society.accounting.chart-of-accounts', [
            'active' => 'chart-of-accounts',
            'tab' => $tab,
            'accounts' => $accounts,
            'groups' => $groups,
            'stats' => $this->coaStats($society, $groups),
            'accountTypes' => ['group' => 'Group', 'detail' => 'Detail'],
        ]);
    }

    public function createAccount(): View
    {
        $society = $this->currentSociety();

        return view('society.accounting.account-create', [
            'active' => 'chart-of-accounts',
            'groups' => AccountGroup::when($society, fn ($q) => $q->where('society_id', $society->id))->orderBy('name')->get(),
            'parents' => Account::when($society, fn ($q) => $q->where('society_id', $society->id))->where('type', 'group')->orderBy('code')->get(),
        ]);
    }

    public function storeAccount(StoreAccountRequest $request): RedirectResponse
    {
        $society = $this->currentSociety();
        $data = $request->validated();

        $account = Account::create([
            'society_id' => $society?->id,
            'code' => $data['code'],
            'name' => $data['name'],
            'group_id' => $data['group_id'],
            'parent_id' => $data['parent_id'] ?? null,
            'type' => $data['type'],
            'opening_balance' => $data['opening_balance'] ?? 0,
            'balance' => $data['opening_balance'] ?? 0,
            'status' => $data['status'],
            'display_order' => (Account::max('display_order') ?? 0) + 1,
        ]);

        return redirect()->route('society.accounting.chart-of-accounts')
            ->with('success', "Account {$account->code} - {$account->name} created successfully.");
    }

    public function openingBalances(): View
    {
        $society = $this->currentSociety();

        $accounts = Account::query()
            ->with('group')
            ->when($society, fn ($q) => $q->where('society_id', $society->id))
            ->where('type', 'detail')
            ->orderBy('code')
            ->get();

        return view('society.accounting.opening-balances', [
            'active' => 'opening-balances',
            'accounts' => $accounts,
        ]);
    }

    /* -------------------------------------------------------------------------
     |  Shared option data
     |------------------------------------------------------------------------- */

    /**
     * @return Collection<int, Account>
     */
    private function detailAccounts(?Society $society)
    {
        return Account::query()
            ->when($society, fn ($q) => $q->where('society_id', $society->id))
            ->where('type', 'detail')
            ->orderBy('code')
            ->get();
    }

    /**
     * @return array<int, array<string, string>>
     */
    private function receiptTypes(): array
    {
        return [
            ['value' => 'maintenance', 'label' => 'Maintenance'],
            ['value' => 'other_charges', 'label' => 'Other Charges'],
            ['value' => 'amenities', 'label' => 'Amenities'],
            ['value' => 'interest_penalty', 'label' => 'Interest & Penalty'],
        ];
    }

    private function nextReceiptNo(): string
    {
        $next = (Receipt::max('id') ?? 0) + 1;

        return 'RCPT/25-26/'.str_pad((string) $next, 4, '0', STR_PAD_LEFT);
    }

    private function nextPaymentNo(): string
    {
        $next = (AccountingPayment::max('id') ?? 0) + 1;

        return 'PMT/25-26/'.str_pad((string) $next, 4, '0', STR_PAD_LEFT);
    }

    private function nextJournalNo(): string
    {
        $next = (JournalEntry::max('id') ?? 0) + 1;

        return 'JV/2505/'.str_pad((string) $next, 3, '0', STR_PAD_LEFT);
    }

    /* -------------------------------------------------------------------------
     |  Demo figures (stat cards / rails) matching the PNGs
     |------------------------------------------------------------------------- */

    /**
     * @return array<string, string>
     */
    private function dashboardStats(): array
    {
        return [
            'total_balance' => '14,85,320.50',
            'total_income' => '9,32,450.00',
            'total_expenses' => '6,48,120.00',
            'total_receivables' => '3,25,600.00',
            'total_payables' => '1,15,750.00',
        ];
    }

    /**
     * @return array<string, string>
     */
    private function transactionStats(): array
    {
        return [
            'total' => '162',
            'receipts' => '9,32,450.00',
            'payments' => '7,98,120.00',
            'journals' => '26',
            'net_balance' => '1,34,330.00',
        ];
    }

    /**
     * @return array<string, string>
     */
    private function receiptStats(): array
    {
        return [
            'total' => '248',
            'total_amount' => '3,25,600.00',
            'pending' => '12',
            'pending_amount' => '45,250.00',
            'average' => '4,256.50',
        ];
    }

    /**
     * @return array<string, string>
     */
    private function paymentStats(): array
    {
        return [
            'total' => '86',
            'total_amount' => '7,98,120.00',
            'pending' => '8',
            'pending_amount' => '38,400.00',
            'average' => '9,280.00',
        ];
    }

    /**
     * @return array<string, string>
     */
    private function journalStats(): array
    {
        return [
            'total' => '26',
            'total_debit' => '4,85,600.00',
            'total_credit' => '4,85,600.00',
            'posted' => '24',
        ];
    }

    /**
     * @return array<int, array<string, string>>
     */
    private function bankAccounts(): array
    {
        return [
            ['name' => 'SBI Bank A/c', 'sub' => 'A/c No. XXXX 4567', 'amount' => '8,25,450.00', 'color' => 'blue', 'icon' => 'fa-building-columns'],
            ['name' => 'HDFC Bank A/c', 'sub' => 'A/c No. XXXX 7890', 'amount' => '5,12,320.00', 'color' => 'red', 'icon' => 'fa-building-columns'],
            ['name' => 'Cash in Hand', 'sub' => 'Petty Cash', 'amount' => '1,47,550.50', 'color' => 'green', 'icon' => 'fa-money-bill-wave'],
        ];
    }

    /**
     * @return array<int, array<string, string>>
     */
    private function receiptSummaryRail(): array
    {
        return [
            ['label' => 'Maintenance Receipts', 'amount' => '2,45,600', 'color' => 'var(--success)'],
            ['label' => 'Other Charges', 'amount' => '45,250', 'color' => 'var(--info)'],
            ['label' => 'Amenities', 'amount' => '28,750', 'color' => 'var(--orange)'],
            ['label' => 'Interest & Penalty', 'amount' => '6,000', 'color' => 'var(--purple)'],
        ];
    }

    /**
     * @return array<int, array<string, string>>
     */
    private function paymentModesRail(): array
    {
        return [
            ['label' => 'UPI', 'amount' => '1,48,500', 'pct' => '45.60%', 'icon' => 'fa-mobile-screen'],
            ['label' => 'Net Banking', 'amount' => '1,02,750', 'pct' => '31.54%', 'icon' => 'fa-building-columns'],
            ['label' => 'Card', 'amount' => '56,850', 'pct' => '17.44%', 'icon' => 'fa-credit-card'],
            ['label' => 'Cash', 'amount' => '17,500', 'pct' => '5.38%', 'icon' => 'fa-money-bill-wave'],
        ];
    }

    /**
     * @param  Collection<int, AccountGroup>  $groups
     * @return array<string, mixed>
     */
    private function coaStats(?Society $society, $groups): array
    {
        $base = Account::query()->when($society, fn ($q) => $q->where('society_id', $society->id));
        $total = (clone $base)->count();
        $active = (clone $base)->where('status', 'active')->count();
        $inactive = (clone $base)->where('status', 'inactive')->count();

        return [
            'total' => $total,
            'active' => $active,
            'active_pct' => $total > 0 ? number_format($active / $total * 100, 2).'% of total' : '0%',
            'inactive' => $inactive,
            'inactive_pct' => $total > 0 ? number_format($inactive / $total * 100, 2).'% of total' : '0%',
            'groups' => $groups->count(),
        ];
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    private function reconciliationRows(): array
    {
        return [
            ['date' => '28 May 2025', 'description' => 'Maintenance - A-101', 'book' => '8,500.00', 'bank' => '8,500.00', 'matched' => true],
            ['date' => '27 May 2025', 'description' => 'Electricity Bill', 'book' => '42,000.00', 'bank' => '42,000.00', 'matched' => true],
            ['date' => '26 May 2025', 'description' => 'Housekeeping Charges', 'book' => '18,000.00', 'bank' => '0.00', 'matched' => false],
            ['date' => '25 May 2025', 'description' => 'Amenities - Clubhouse', 'book' => '3,500.00', 'bank' => '3,500.00', 'matched' => true],
            ['date' => '24 May 2025', 'description' => 'Bank Charges', 'book' => '0.00', 'bank' => '250.00', 'matched' => false],
        ];
    }
}
