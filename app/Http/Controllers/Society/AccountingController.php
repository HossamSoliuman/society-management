<?php

namespace App\Http\Controllers\Society;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreAccountingPaymentRequest;
use App\Http\Requests\StoreAccountRequest;
use App\Http\Requests\StoreJournalEntryRequest;
use App\Http\Requests\StoreReceiptRequest;
use App\Imports\BankStatementImport;
use App\Models\Account;
use App\Models\AccountGroup;
use App\Models\AccountingPayment;
use App\Models\BankStatementLine;
use App\Models\JournalEntry;
use App\Models\Receipt;
use App\Models\Society;
use App\Models\Transaction;
use App\Services\AccountingService;
use App\Services\BankReconciliationService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;
use Maatwebsite\Excel\Facades\Excel;

class AccountingController extends Controller
{
    /** Payment modes offered across the accounting create forms and filters. */
    private const PAYMENT_MODES = ['UPI', 'Card', 'Net Banking', 'Cheque', 'Cash'];

    public function __construct(
        private readonly AccountingService $accounting,
        private readonly BankReconciliationService $reconciliation,
    ) {}

    public function index(): View
    {
        $society = $this->currentSociety();

        $recent = Transaction::query()
            ->with('account')
            ->forSociety($society)
            ->orderByDesc('date')
            ->orderByDesc('id')
            ->limit(5)
            ->get();

        return view('society.accounting.index', [
            'active' => 'dashboard',
            'stats' => $this->accounting->dashboardStats($society),
            'recent' => $recent,
            'cashFlow' => $this->accounting->cashFlowSeries($society),
            'balanceSummary' => $this->accounting->accountBalanceSummary($society),
            'bankAccounts' => $this->accounting->bankAccounts($society),
            'accountsForFilter' => $this->detailAccounts($society),
            'paymentModes' => self::PAYMENT_MODES,
            'locations' => $this->locations($society),
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
            ->forSociety($society)
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
            'stats' => $this->transactionStats($society),
            'summary' => $this->accounting->transactionSummary($society),
            'accountsForFilter' => $this->detailAccounts($society),
            'paymentModes' => self::PAYMENT_MODES,
            'locations' => $this->locations($society),
        ]);
    }

    public function receipts(Request $request): View
    {
        $society = $this->currentSociety();

        $receipts = Receipt::query()
            ->with('account')
            ->forSociety($society)
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
            'stats' => $this->receiptStats($society),
            'receiptSummary' => $this->receiptSummaryRail($society),
            'paymentModesRail' => $this->paymentModesRail($society),
            'accountsForFilter' => $this->detailAccounts($society),
            'receiptTypes' => $this->receiptTypes(),
            'locations' => $this->locations($society),
        ]);
    }

    public function createReceipt(): View
    {
        $society = $this->currentSociety();

        return view('society.accounting.receipts-create', [
            'active' => 'receipts',
            'accounts' => $this->settlementAccounts($society),
            'incomeAccounts' => $this->accountsOfKind($society, 'income'),
            'receiptTypes' => $this->receiptTypes(),
            'paymentModes' => self::PAYMENT_MODES,
            'locations' => $this->locations($society),
        ]);
    }

    public function storeReceipt(StoreReceiptRequest $request): RedirectResponse
    {
        $society = $this->currentSociety();
        $data = $request->validated();

        $receipt = Receipt::create($data + [
            'society_id' => $society->id,
            'receipt_no' => $this->accounting->nextNumber($society, 'accounting_receipt'),
            'status' => 'completed',
        ]);

        return redirect()->route('society.accounting.receipts')
            ->with('success', "Receipt {$receipt->receipt_no} recorded and posted to the ledger.");
    }

    public function payments(Request $request): View
    {
        $society = $this->currentSociety();

        $payments = AccountingPayment::query()
            ->with('account')
            ->forSociety($society)
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
            'stats' => $this->paymentStats($society),
            'accountsForFilter' => $this->detailAccounts($society),
            'paymentModes' => self::PAYMENT_MODES,
        ]);
    }

    public function createPayment(): View
    {
        $society = $this->currentSociety();

        return view('society.accounting.payments-create', [
            'active' => 'payments',
            'accounts' => $this->settlementAccounts($society),
            'expenseAccounts' => $this->accountsOfKind($society, 'expense'),
            'paymentModes' => self::PAYMENT_MODES,
        ]);
    }

    public function storePayment(StoreAccountingPaymentRequest $request): RedirectResponse
    {
        $society = $this->currentSociety();
        $data = $request->validated();

        $payment = AccountingPayment::create($data + [
            'society_id' => $society->id,
            'payment_no' => $this->accounting->nextNumber($society, 'accounting_payment'),
            'status' => 'completed',
        ]);

        return redirect()->route('society.accounting.payments')
            ->with('success', "Payment {$payment->payment_no} recorded and posted to the ledger.");
    }

    public function journalEntries(Request $request): View
    {
        $society = $this->currentSociety();

        $entries = JournalEntry::query()
            ->withCount('lines')
            ->forSociety($society)
            ->when($request->filled('q'), function ($q) use ($request) {
                $term = $request->string('q');
                $q->where(fn ($sub) => $sub->where('entry_no', 'like', "%{$term}%")->orWhere('narration', 'like', "%{$term}%"));
            })
            ->orderByDesc('date')
            ->orderByDesc('id')
            ->paginate(10)
            ->withQueryString();

        return view('society.accounting.journal-entries', [
            'active' => 'journal-entries',
            'entries' => $entries,
            'stats' => $this->journalStats($society),
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
        $totalDebit = round($lines->sum(fn ($line) => (float) ($line['debit'] ?? 0)), 2);
        $totalCredit = round($lines->sum(fn ($line) => (float) ($line['credit'] ?? 0)), 2);

        if (abs($totalDebit - $totalCredit) > 0.005) {
            throw ValidationException::withMessages(['lines' => "Debits ({$totalDebit}) must equal credits ({$totalCredit})."]);
        }

        $entry = DB::transaction(function () use ($society, $data, $lines, $totalDebit, $totalCredit): JournalEntry {
            $entry = JournalEntry::create([
                'society_id' => $society->id,
                'entry_no' => $this->accounting->nextNumber($society, 'journal'),
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

            $this->accounting->postJournalEntry($entry);

            return $entry;
        });

        return redirect()->route('society.accounting.journal-entries')
            ->with('success', "Journal entry {$entry->entry_no} posted successfully.");
    }

    public function bankReconciliation(Request $request): View
    {
        $society = $this->currentSociety();
        $bankAccounts = $this->bankAccountModels($society);
        $account = $request->filled('account')
            ? $bankAccounts->firstWhere('id', $request->integer('account'))
            : $bankAccounts->first();

        $from = $request->filled('from') ? Carbon::parse($request->string('from')) : null;
        $to = $request->filled('to') ? Carbon::parse($request->string('to')) : null;

        $overview = $account
            ? $this->reconciliation->overview($society, $account, $from, $to)
            : ['rows' => collect(), 'summary' => null];

        return view('society.accounting.bank-reconciliation', [
            'active' => 'bank-reconciliation',
            'bankAccounts' => $bankAccounts,
            'account' => $account,
            'rows' => $overview['rows'],
            'summary' => $overview['summary'],
            'from' => $from?->toDateString(),
            'to' => $to?->toDateString(),
        ]);
    }

    public function importBankStatement(Request $request): RedirectResponse
    {
        $society = $this->currentSociety();

        $data = $request->validate([
            'account_id' => ['required', 'integer', Rule::exists('accounts', 'id')->where('society_id', $society->id)],
            'file' => ['required', 'file', 'mimes:csv,txt,xlsx,xls', 'max:5120'],
        ]);

        $account = Account::query()->forSociety($society)->findOrFail($data['account_id']);

        $import = new BankStatementImport;
        Excel::import($import, $request->file('file'));

        if ($import->rows === []) {
            return back()->with('error', 'No statement lines could be read from the file.');
        }

        $result = $this->reconciliation->import($society, $account, $import->rows);

        return redirect()->route('society.accounting.bank-reconciliation', ['account' => $account->id])
            ->with('success', "{$result['imported']} statement line(s) imported, {$result['matched']} matched automatically.".(count($import->errors) ? ' '.count($import->errors).' row(s) skipped.' : ''));
    }

    public function matchBankLine(Request $request, BankStatementLine $line): RedirectResponse
    {
        $society = $this->currentSociety();
        $data = $request->validate([
            'transaction_id' => ['required', 'integer', Rule::exists('transactions', 'id')->where('society_id', $society->id)->where('account_id', $line->account_id)],
        ]);

        $this->reconciliation->link($line, Transaction::findOrFail($data['transaction_id']));

        return back()->with('success', 'Statement line matched.');
    }

    public function unmatchBankLine(BankStatementLine $line): RedirectResponse
    {
        $this->reconciliation->unlink($line);

        return back()->with('success', 'Statement line unmatched.');
    }

    public function trialBalance(Request $request): View
    {
        $society = $this->currentSociety();
        $asOn = $request->filled('as_on') ? Carbon::parse($request->string('as_on')) : Carbon::today();

        return view('society.accounting.trial-balance', [
            'active' => 'trial-balance',
            'trialBalance' => $this->accounting->trialBalance($society, $asOn),
            'asOn' => $asOn->toDateString(),
        ]);
    }

    public function profitLoss(Request $request): View
    {
        $society = $this->currentSociety();
        [$from, $to] = $this->periodFromRequest($request);
        $compareFrom = $request->filled('compare_from') ? Carbon::parse($request->string('compare_from')) : null;
        $compareTo = $request->filled('compare_to') ? Carbon::parse($request->string('compare_to')) : null;

        return view('society.accounting.profit-loss', [
            'active' => 'profit-loss',
            'pl' => $this->accounting->profitAndLoss($society, $from, $to, $compareFrom, $compareTo),
            'accountsForFilter' => $this->detailAccounts($society),
            'from' => $from->toDateString(),
            'to' => $to->toDateString(),
        ]);
    }

    public function balanceSheet(Request $request): View
    {
        $society = $this->currentSociety();
        $asOn = $request->filled('as_on') ? Carbon::parse($request->string('as_on')) : Carbon::today();
        $compareOn = $request->filled('compare_on') ? Carbon::parse($request->string('compare_on')) : null;

        return view('society.accounting.balance-sheet', [
            'active' => 'balance-sheet',
            'bs' => $this->accounting->balanceSheet($society, $asOn, $compareOn),
            'asOn' => $asOn->toDateString(),
        ]);
    }

    public function chartOfAccounts(Request $request): View
    {
        $society = $this->currentSociety();
        $tab = $request->string('tab')->toString() === 'groups' ? 'groups' : 'list';

        $accounts = Account::query()
            ->with('group')
            ->forSociety($society)
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
            ->forSociety($society)
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
            'groups' => AccountGroup::query()->forSociety($society)->orderBy('name')->get(),
            'parents' => Account::query()->forSociety($society)->where('type', 'group')->orderBy('code')->get(),
        ]);
    }

    public function storeAccount(StoreAccountRequest $request): RedirectResponse
    {
        $society = $this->currentSociety();
        $data = $request->validated();

        $account = Account::create([
            'society_id' => $society->id,
            'code' => $data['code'],
            'name' => $data['name'],
            'group_id' => $data['group_id'],
            'parent_id' => $data['parent_id'] ?? null,
            'type' => $data['type'],
            'is_bank' => $request->boolean('is_bank'),
            'opening_balance' => $data['opening_balance'] ?? 0,
            'balance' => $data['opening_balance'] ?? 0,
            'status' => $data['status'],
            'display_order' => (int) Account::query()->forSociety($society)->max('display_order') + 1,
        ]);

        return redirect()->route('society.accounting.chart-of-accounts')
            ->with('success', "Account {$account->code} - {$account->name} created successfully.");
    }

    public function openingBalances(): View
    {
        $society = $this->currentSociety();

        $accounts = Account::query()
            ->with('group')
            ->forSociety($society)
            ->where('type', 'detail')
            ->orderBy('code')
            ->get();

        $fyStart = $this->accounting->financialYearStart(Carbon::today());

        return view('society.accounting.opening-balances', [
            'active' => 'opening-balances',
            'accounts' => $accounts,
            'financialYear' => $this->accounting->financialYearLabel(Carbon::today()),
            'fyStart' => $fyStart->toDateString(),
            'openingEntry' => JournalEntry::query()->forSociety($society)->where('is_opening', true)->latest('id')->first(),
        ]);
    }

    /**
     * Save opening balances: writes them to accounts.opening_balance and posts
     * (or re-posts) an opening journal entry dated at the financial-year start,
     * balanced through Opening Balance Equity.
     */
    public function updateOpeningBalances(Request $request): RedirectResponse
    {
        $society = $this->currentSociety();

        $data = $request->validate([
            'financial_year_start' => ['required', 'date'],
            'balances' => ['required', 'array'],
            'balances.*' => ['nullable', 'numeric'],
        ]);

        $fyStart = Carbon::parse($data['financial_year_start']);
        $equity = $this->accounting->defaultAccount($society, 'opening_equity');

        $accounts = Account::query()->with('group')->forSociety($society)->where('type', 'detail')->get()->keyBy('id');

        DB::transaction(function () use ($society, $data, $fyStart, $equity, $accounts) {
            $lines = [];
            $debitTotal = 0.0;
            $creditTotal = 0.0;

            foreach ($data['balances'] as $accountId => $amount) {
                $account = $accounts->get((int) $accountId);
                if (! $account || $account->id === $equity->id) {
                    continue;
                }
                $amount = round((float) $amount, 2);
                $account->forceFill(['opening_balance' => 0])->saveQuietly();
                if ($amount == 0.0) {
                    continue;
                }

                $debitNature = $this->accounting->isDebitNature($account);
                $debit = ($debitNature && $amount > 0) || (! $debitNature && $amount < 0) ? abs($amount) : 0.0;
                $credit = $debit > 0 ? 0.0 : abs($amount);
                $lines[] = ['account_id' => $account->id, 'debit' => $debit, 'credit' => $credit];
                $debitTotal += $debit;
                $creditTotal += $credit;
            }

            $difference = round($debitTotal - $creditTotal, 2);
            if ($difference > 0) {
                $lines[] = ['account_id' => $equity->id, 'credit' => $difference];
            } elseif ($difference < 0) {
                $lines[] = ['account_id' => $equity->id, 'debit' => abs($difference)];
            }

            $entry = JournalEntry::query()->forSociety($society)->where('is_opening', true)->first();
            if ($entry) {
                $this->accounting->unpost($entry);
                $entry->lines()->delete();
            } else {
                $entry = new JournalEntry([
                    'society_id' => $society->id,
                    'entry_no' => $this->accounting->nextNumber($society, 'journal'),
                    'is_opening' => true,
                ]);
            }

            $entry->forceFill([
                'date' => $fyStart->toDateString(),
                'narration' => 'Opening balances as on '.$fyStart->format('d M Y'),
                'total_debit' => round(max($debitTotal, $creditTotal), 2),
                'total_credit' => round(max($debitTotal, $creditTotal), 2),
                'status' => 'posted',
            ])->save();

            foreach ($lines as $line) {
                $entry->lines()->create([
                    'account_id' => $line['account_id'],
                    'debit' => $line['debit'] ?? 0,
                    'credit' => $line['credit'] ?? 0,
                ]);
            }

            if ($lines !== []) {
                $this->accounting->postJournalEntry($entry);
            }

            $this->accounting->refreshBalances($society);
        });

        return redirect()->route('society.accounting.opening-balances')
            ->with('success', 'Opening balances saved and posted as journal entry dated '.$fyStart->format('d M Y').'.');
    }

    /* -------------------------------------------------------------------------
     |  Shared option data
     |------------------------------------------------------------------------- */

    /**
     * @return Collection<int, Account>
     */
    private function detailAccounts(Society $society): Collection
    {
        return $this->accounting->detailAccounts($society);
    }

    /**
     * Cash and bank accounts money is received into / paid out of.
     *
     * @return Collection<int, Account>
     */
    private function settlementAccounts(Society $society): Collection
    {
        $accounts = $this->detailAccounts($society)
            ->filter(fn (Account $a) => $a->is_bank || in_array($a->system_key, ['cash', 'bank'], true))
            ->values();

        return $accounts->isNotEmpty() ? $accounts : $this->detailAccounts($society);
    }

    /**
     * @return Collection<int, Account>
     */
    private function accountsOfKind(Society $society, string $kind): Collection
    {
        return $this->detailAccounts($society)
            ->filter(fn (Account $a) => ($a->group?->kind ?? $this->accounting->kindForGroupName((string) $a->group?->name)) === $kind)
            ->values();
    }

    /**
     * @return Collection<int, Account>
     */
    private function bankAccountModels(Society $society): Collection
    {
        return $this->detailAccounts($society)->filter(fn (Account $a) => $a->is_bank || $a->system_key === 'bank')->values();
    }

    /**
     * Locations used on existing rows (towers/buildings) for the filters.
     *
     * @return array<int, string>
     */
    private function locations(Society $society): array
    {
        return Transaction::query()->forSociety($society)->whereNotNull('location')->distinct()->orderBy('location')->pluck('location')->all();
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

    /**
     * @return array{0: Carbon, 1: Carbon}
     */
    private function periodFromRequest(Request $request): array
    {
        $to = $request->filled('to') ? Carbon::parse($request->string('to')) : Carbon::today();
        $from = $request->filled('from') ? Carbon::parse($request->string('from')) : $this->accounting->financialYearStart($to);

        return [$from, $to];
    }

    /* -------------------------------------------------------------------------
     |  Stat cards (all computed from the ledger / source documents)
     |------------------------------------------------------------------------- */

    /**
     * @return array<string, string>
     */
    private function transactionStats(Society $society): array
    {
        $base = Transaction::query()->forSociety($society);
        $receipts = (float) (clone $base)->where('type', 'receipt')->sum('debit');
        $payments = (float) (clone $base)->where('type', 'payment')->sum('credit');

        return [
            'total' => number_format((clone $base)->count()),
            'receipts' => number_format($receipts, 2),
            'payments' => number_format($payments, 2),
            'journals' => number_format(JournalEntry::query()->forSociety($society)->count()),
            'net_balance' => number_format($receipts - $payments, 2),
        ];
    }

    /**
     * @return array<string, string>
     */
    private function receiptStats(Society $society): array
    {
        $base = Receipt::query()->forSociety($society);
        $count = (clone $base)->count();
        $total = (float) (clone $base)->where('status', 'completed')->sum('amount');

        return [
            'total' => number_format($count),
            'total_amount' => number_format($total, 2),
            'pending' => number_format((clone $base)->where('status', 'pending')->count()),
            'pending_amount' => number_format((float) (clone $base)->where('status', 'pending')->sum('amount'), 2),
            'average' => number_format($count > 0 ? $total / $count : 0, 2),
        ];
    }

    /**
     * @return array<string, string>
     */
    private function paymentStats(Society $society): array
    {
        $base = AccountingPayment::query()->forSociety($society);
        $count = (clone $base)->count();
        $total = (float) (clone $base)->where('status', 'completed')->sum('amount');

        return [
            'total' => number_format($count),
            'total_amount' => number_format($total, 2),
            'pending' => number_format((clone $base)->where('status', 'pending')->count()),
            'pending_amount' => number_format((float) (clone $base)->where('status', 'pending')->sum('amount'), 2),
            'average' => number_format($count > 0 ? $total / $count : 0, 2),
        ];
    }

    /**
     * @return array<string, string>
     */
    private function journalStats(Society $society): array
    {
        $base = JournalEntry::query()->forSociety($society);

        return [
            'total' => number_format((clone $base)->count()),
            'total_debit' => number_format((float) (clone $base)->sum('total_debit'), 2),
            'total_credit' => number_format((float) (clone $base)->sum('total_credit'), 2),
            'posted' => number_format((clone $base)->where('status', 'posted')->count()),
        ];
    }

    /**
     * @return array<int, array<string, string>>
     */
    private function receiptSummaryRail(Society $society): array
    {
        $byType = Receipt::query()->forSociety($society)->where('status', 'completed')
            ->selectRaw('receipt_type, COALESCE(SUM(amount),0) as total')
            ->groupBy('receipt_type')
            ->pluck('total', 'receipt_type');

        $labels = [
            'maintenance' => ['Maintenance Receipts', 'var(--success)'],
            'other_charges' => ['Other Charges', 'var(--info)'],
            'amenities' => ['Amenities', 'var(--orange)'],
            'interest_penalty' => ['Interest & Penalty', 'var(--purple)'],
        ];

        return collect($labels)->map(fn ($meta, $type) => [
            'label' => $meta[0],
            'amount' => number_format((float) ($byType[$type] ?? 0)),
            'color' => $meta[1],
        ])->values()->all();
    }

    /**
     * @return array<int, array<string, string>>
     */
    private function paymentModesRail(Society $society): array
    {
        $rows = Receipt::query()->forSociety($society)->where('status', 'completed')
            ->selectRaw('mode_of_payment, COALESCE(SUM(amount),0) as total')
            ->groupBy('mode_of_payment')
            ->orderByDesc('total')
            ->get();
        $total = (float) $rows->sum('total');
        $icons = ['UPI' => 'fa-mobile-screen', 'Net Banking' => 'fa-building-columns', 'Card' => 'fa-credit-card', 'Cash' => 'fa-money-bill-wave', 'Cheque' => 'fa-money-check'];

        return $rows->map(fn ($r) => [
            'label' => $r->mode_of_payment ?: 'Other',
            'amount' => number_format((float) $r->total),
            'pct' => ($total > 0 ? number_format((float) $r->total / $total * 100, 2) : '0.00').'%',
            'icon' => $icons[$r->mode_of_payment] ?? 'fa-wallet',
        ])->all();
    }

    /**
     * @param  Collection<int, AccountGroup>  $groups
     * @return array<string, mixed>
     */
    private function coaStats(Society $society, Collection $groups): array
    {
        $base = Account::query()->forSociety($society);
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
}
