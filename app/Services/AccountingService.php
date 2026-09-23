<?php

namespace App\Services;

use App\Models\Account;
use App\Models\AccountGroup;
use App\Models\JournalEntry;
use App\Models\NumberingSeries;
use App\Models\Society;
use App\Models\Transaction;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

/**
 * Double-entry ledger for a society: every receipt, payment, journal entry,
 * collection and expense posts balanced `transactions` rows, and every
 * statement (trial balance, P&L, balance sheet, dashboard figures) is derived
 * from those rows plus account opening balances.
 */
class AccountingService
{
    /** Account groups by accounting nature; debit-nature kinds increase with debits. */
    public const DEBIT_KINDS = ['asset', 'expense'];

    public const CREDIT_KINDS = ['liability', 'income', 'equity'];

    /**
     * Default chart of accounts. [system_key => [code, name, group, is_bank]]
     *
     * @var array<string, array{0: string, 1: string, 2: string, 3?: bool}>
     */
    public const DEFAULT_ACCOUNTS = [
        'cash' => ['1110', 'Cash in Hand', 'Assets'],
        'bank' => ['1121', 'Bank Account', 'Assets', true],
        'receivables' => ['1310', 'Member Receivables', 'Assets'],
        'fixed_assets' => ['1210', 'Fixed Assets', 'Assets'],
        'payables' => ['2110', 'Sundry Creditors', 'Liabilities'],
        'deposits' => ['2210', 'Member Deposits', 'Liabilities'],
        'opening_equity' => ['3100', 'Opening Balance Equity', 'Equity'],
        'reserve_fund' => ['3200', 'Reserve Fund', 'Equity'],
        'sinking_fund' => ['3300', 'Sinking Fund', 'Equity'],
        'maintenance_income' => ['4110', 'Maintenance Income', 'Income'],
        'other_income' => ['4210', 'Other Charges Income', 'Income'],
        'amenities_income' => ['4310', 'Amenities Income', 'Income'],
        'interest_income' => ['4410', 'Interest & Penalty Income', 'Income'],
        'general_expense' => ['5110', 'General Expenses', 'Expenses'],
        'maintenance_expense' => ['5210', 'Repairs & Maintenance', 'Expenses'],
        'utility_expense' => ['5310', 'Utilities', 'Expenses'],
        'admin_expense' => ['5410', 'Administrative Expenses', 'Expenses'],
        'salary_expense' => ['5510', 'Salaries & Wages', 'Expenses'],
    ];

    /**
     * Income account used for each receipt type.
     *
     * @var array<string, string>
     */
    public const RECEIPT_TYPE_ACCOUNTS = [
        'maintenance' => 'maintenance_income',
        'other_charges' => 'other_income',
        'amenities' => 'amenities_income',
        'interest_penalty' => 'interest_income',
    ];

    /* ----------------------------------------------------------------------
     |  Chart of accounts
     |---------------------------------------------------------------------- */

    /**
     * Create the default groups + accounts for a society (idempotent).
     */
    public function seedChartFor(Society $society): void
    {
        $groups = [
            'Assets' => ['asset', 'green', 'fa-building'],
            'Liabilities' => ['liability', 'blue', 'fa-hand-holding-dollar'],
            'Income' => ['income', 'purple', 'fa-arrow-down'],
            'Expenses' => ['expense', 'orange', 'fa-arrow-up'],
            'Equity' => ['equity', 'blue', 'fa-scale-balanced'],
        ];

        foreach ($groups as $name => [$kind, $color, $icon]) {
            AccountGroup::query()->firstOrCreate(
                ['society_id' => $society->id, 'name' => $name],
                ['kind' => $kind, 'color' => $color, 'icon' => $icon],
            );
        }

        foreach (self::DEFAULT_ACCOUNTS as $key => $definition) {
            $this->defaultAccount($society, $key);
        }
    }

    /**
     * Find (or lazily create) the society account for a system key.
     */
    public function defaultAccount(Society $society, string $key): Account
    {
        $existing = Account::query()->forSociety($society)->where('system_key', $key)->first();
        if ($existing) {
            return $existing;
        }

        [$code, $name, $groupName] = self::DEFAULT_ACCOUNTS[$key] ?? throw new InvalidArgumentException("Unknown system account [{$key}].");
        $isBank = (bool) (self::DEFAULT_ACCOUNTS[$key][3] ?? false);
        $group = AccountGroup::query()->firstOrCreate(
            ['society_id' => $society->id, 'name' => $groupName],
            ['kind' => $this->kindForGroupName($groupName)],
        );

        // Adopt an existing account with the same name (seeded data) before creating one.
        $byName = Account::query()->forSociety($society)->where('type', 'detail')->where('name', $name)->first();
        if ($byName) {
            $byName->forceFill(['system_key' => $key, 'is_bank' => $isBank || $byName->is_bank])->save();

            return $byName;
        }

        if ($key === 'bank') {
            $bank = Account::query()->forSociety($society)->where('type', 'detail')->where('is_bank', true)->orderBy('code')->first()
                ?? Account::query()->forSociety($society)->where('type', 'detail')->where('name', 'like', '%Bank%')->orderBy('code')->first();
            if ($bank) {
                $bank->forceFill(['system_key' => 'bank', 'is_bank' => true])->save();

                return $bank;
            }
        }

        $code = Account::query()->forSociety($society)->where('code', $code)->exists() ? $code.'-'.substr($key, 0, 3) : $code;

        return Account::create([
            'society_id' => $society->id,
            'code' => $code,
            'name' => $name,
            'system_key' => $key,
            'is_bank' => $isBank,
            'group_id' => $group->id,
            'type' => 'detail',
            'opening_balance' => 0,
            'balance' => 0,
            'status' => 'active',
            'display_order' => (int) Account::query()->forSociety($society)->max('display_order') + 1,
        ]);
    }

    /**
     * Cash or bank account for a payment mode.
     */
    public function settlementAccount(Society $society, ?string $mode): Account
    {
        return strtolower((string) $mode) === 'cash'
            ? $this->defaultAccount($society, 'cash')
            : $this->defaultAccount($society, 'bank');
    }

    /* ----------------------------------------------------------------------
     |  Posting
     |---------------------------------------------------------------------- */

    /**
     * Post balanced lines to the ledger.
     *
     * @param  array<int, array{account_id: int, debit?: float, credit?: float, description?: ?string}>  $lines
     * @param  array<string, mixed>  $meta  reference_no, description, payment_mode, location
     * @return Collection<int, Transaction>
     */
    public function post(Society $society, Carbon $date, string $type, array $lines, ?Model $source = null, array $meta = []): Collection
    {
        $lines = array_values(array_filter($lines, fn ($l) => round((float) ($l['debit'] ?? 0), 2) > 0 || round((float) ($l['credit'] ?? 0), 2) > 0));
        $debits = round(array_sum(array_map(fn ($l) => (float) ($l['debit'] ?? 0), $lines)), 2);
        $credits = round(array_sum(array_map(fn ($l) => (float) ($l['credit'] ?? 0), $lines)), 2);

        if ($lines === [] || abs($debits - $credits) > 0.005) {
            throw new InvalidArgumentException("Ledger posting is not balanced (Dr {$debits} / Cr {$credits}).");
        }

        return DB::transaction(function () use ($society, $date, $type, $lines, $source, $meta): Collection {
            if ($source) {
                $this->unpost($source);
            }

            $rows = new Collection;
            foreach ($lines as $line) {
                $rows->push(Transaction::create([
                    'society_id' => $society->id,
                    'date' => $date->toDateString(),
                    'type' => $type,
                    'reference_no' => $meta['reference_no'] ?? null,
                    'description' => $line['description'] ?? ($meta['description'] ?? null),
                    'account_id' => $line['account_id'],
                    'payment_mode' => $meta['payment_mode'] ?? null,
                    'debit' => round((float) ($line['debit'] ?? 0), 2),
                    'credit' => round((float) ($line['credit'] ?? 0), 2),
                    'location' => $meta['location'] ?? null,
                    'source_type' => $source?->getMorphClass(),
                    'source_id' => $source?->getKey(),
                ]));
            }

            $this->refreshBalances($society, $rows->pluck('account_id')->unique()->all());

            return $rows;
        });
    }

    /**
     * Remove a source document's ledger rows (before re-posting or on delete).
     */
    public function unpost(Model $source): void
    {
        $rows = Transaction::query()
            ->where('source_type', $source->getMorphClass())
            ->where('source_id', $source->getKey())
            ->get();

        if ($rows->isEmpty()) {
            return;
        }

        $societyId = $rows->first()->society_id;
        Transaction::query()->whereIn('id', $rows->pluck('id'))->delete();

        if ($societyId && ($society = Society::find($societyId))) {
            $this->refreshBalances($society, $rows->pluck('account_id')->unique()->all());
        }
    }

    /**
     * Post a journal entry's lines (used by the journal screen and opening balances).
     */
    public function postJournalEntry(JournalEntry $entry): void
    {
        $entry->loadMissing('lines');
        $lines = $entry->lines->map(fn ($line) => [
            'account_id' => $line->account_id,
            'debit' => (float) $line->debit,
            'credit' => (float) $line->credit,
        ])->all();

        $this->post($entry->society, Carbon::parse($entry->date), 'journal', $lines, $entry, [
            'reference_no' => $entry->entry_no,
            'description' => $entry->narration,
        ]);
    }

    /**
     * Natural (signed) balance of an account as of a date: opening + movements.
     * Debit-nature accounts grow with debits, credit-nature with credits.
     */
    public function balance(Account $account, ?Carbon $upTo = null, ?Carbon $from = null, bool $includeOpening = true): float
    {
        $query = Transaction::query()->where('account_id', $account->id);
        if ($upTo) {
            $query->whereDate('date', '<=', $upTo->toDateString());
        }
        if ($from) {
            $query->whereDate('date', '>=', $from->toDateString());
        }
        $sums = $query->selectRaw('COALESCE(SUM(debit),0) as d, COALESCE(SUM(credit),0) as c')->first();

        $movement = $this->isDebitNature($account)
            ? (float) $sums->d - (float) $sums->c
            : (float) $sums->c - (float) $sums->d;

        return round(($includeOpening ? (float) $account->opening_balance : 0) + $movement, 2);
    }

    /**
     * Sync the denormalised accounts.balance column from the ledger.
     *
     * @param  array<int, int>|null  $accountIds  limit to these accounts
     */
    public function refreshBalances(Society $society, ?array $accountIds = null): void
    {
        Account::query()
            ->forSociety($society)
            ->with('group')
            ->when($accountIds, fn ($q) => $q->whereIn('id', $accountIds))
            ->where('type', 'detail')
            ->each(function (Account $account) {
                $account->forceFill(['balance' => $this->balance($account)])->saveQuietly();
            });
    }

    public function isDebitNature(Account $account): bool
    {
        $kind = $account->group?->kind ?? $this->kindForGroupName((string) $account->group?->name);

        return in_array($kind, self::DEBIT_KINDS, true);
    }

    /* ----------------------------------------------------------------------
     |  Numbering
     |---------------------------------------------------------------------- */

    /**
     * Next document number from the society's NumberingSeries for the type,
     * creating a default series on first use.
     */
    public function nextNumber(Society $society, string $documentType): string
    {
        $series = NumberingSeries::query()
            ->forSociety($society)
            ->where('document_type', $documentType)
            ->where('status', 'active')
            ->orderByDesc('is_default')
            ->lockForUpdate()
            ->first();

        if (! $series) {
            $fy = $this->financialYearLabel(Carbon::today());
            $series = NumberingSeries::create([
                'society_id' => $society->id,
                'document_type' => $documentType,
                'is_default' => true,
                'prefix' => match ($documentType) {
                    'accounting_receipt' => 'RCPT/',
                    'accounting_payment' => 'PMT/',
                    'journal' => 'JV/',
                    default => strtoupper(substr($documentType, 0, 3)).'/',
                },
                'format' => 'YY-####',
                'next_number' => 1,
                'reset_frequency' => 'yearly',
                'financial_year' => $fy,
                'description' => 'Auto-created series',
                'status' => 'active',
            ]);
        }

        return $series->generateNext();
    }

    /**
     * "2026-2027" style label for the Indian financial year containing $date.
     */
    public function financialYearLabel(Carbon $date): string
    {
        $start = $date->month >= 4 ? $date->year : $date->year - 1;

        return $start.'-'.($start + 1);
    }

    public function financialYearStart(Carbon $date): Carbon
    {
        $year = $date->month >= 4 ? $date->year : $date->year - 1;

        return Carbon::create($year, 4, 1)->startOfDay();
    }

    /* ----------------------------------------------------------------------
     |  Statements
     |---------------------------------------------------------------------- */

    /**
     * Trial balance for the society (optionally as of a date). Every detail
     * account with a non-zero natural balance lands in exactly one column.
     *
     * @return array{rows: array<int, array{code: string, name: string, group: string, debit: float, credit: float}>, total_debit: float, total_credit: float, as_on: string}
     */
    public function trialBalance(Society $society, ?Carbon $asOn = null): array
    {
        $asOn ??= Carbon::today();
        $rows = [];
        $totalDebit = 0.0;
        $totalCredit = 0.0;

        foreach ($this->detailAccounts($society) as $account) {
            $balance = $this->balance($account, $asOn);
            if (abs($balance) < 0.005) {
                continue;
            }

            $debitSide = $this->isDebitNature($account) ? $balance >= 0 : $balance < 0;
            $amount = abs($balance);

            $rows[] = [
                'code' => $account->code,
                'name' => $account->name,
                'group' => $account->group?->name ?? '',
                'debit' => $debitSide ? $amount : 0.0,
                'credit' => $debitSide ? 0.0 : $amount,
            ];
            $totalDebit += $debitSide ? $amount : 0.0;
            $totalCredit += $debitSide ? 0.0 : $amount;
        }

        return [
            'rows' => $rows,
            'total_debit' => round($totalDebit, 2),
            'total_credit' => round($totalCredit, 2),
            'as_on' => $asOn->format('d M Y'),
        ];
    }

    /**
     * Income statement for a period with a comparison period.
     *
     * @return array<string, mixed>
     */
    public function profitAndLoss(Society $society, Carbon $from, Carbon $to, ?Carbon $compareFrom = null, ?Carbon $compareTo = null): array
    {
        $days = max(1, (int) $from->diffInDays($to) + 1);
        $compareTo ??= $from->copy()->subDay();
        $compareFrom ??= $compareTo->copy()->subDays($days - 1);

        $income = $this->periodMovements($society, 'income', $from, $to);
        $incomePrev = $this->periodMovements($society, 'income', $compareFrom, $compareTo);
        $expenses = $this->periodMovements($society, 'expense', $from, $to);
        $expensesPrev = $this->periodMovements($society, 'expense', $compareFrom, $compareTo);

        $totalIncome = round($income->sum('amount'), 2);
        $totalIncomePrev = round($incomePrev->sum('amount'), 2);
        $totalExpenses = round($expenses->sum('amount'), 2);
        $totalExpensesPrev = round($expensesPrev->sum('amount'), 2);
        $net = round($totalIncome - $totalExpenses, 2);
        $netPrev = round($totalIncomePrev - $totalExpensesPrev, 2);
        $margin = $totalIncome > 0 ? round($net / $totalIncome * 100, 2) : 0.0;
        $marginPrev = $totalIncomePrev > 0 ? round($netPrev / $totalIncomePrev * 100, 2) : 0.0;

        $pct = fn (float $now, float $then) => $then != 0 ? round(abs($now - $then) / abs($then) * 100, 1) : ($now != 0 ? 100.0 : 0.0);
        $dir = fn (float $now, float $then) => $now >= $then ? 'up' : 'down';

        $rowFor = function (string $name, float $now, float $then) use ($pct, $dir): array {
            return [$name, number_format($now, 2), number_format($then, 2), number_format($now - $then, 2), $pct($now, $then).'%', $dir($now, $then)];
        };

        $incomeRows = [];
        foreach ($income->keyBy('account_id') as $id => $row) {
            $prev = (float) ($incomePrev->firstWhere('account_id', $id)['amount'] ?? 0);
            $incomeRows[] = $rowFor($row['name'], (float) $row['amount'], $prev);
        }
        $expenseRows = [];
        foreach ($expenses->keyBy('account_id') as $id => $row) {
            $prev = (float) ($expensesPrev->firstWhere('account_id', $id)['amount'] ?? 0);
            $expenseRows[] = $rowFor($row['name'], (float) $row['amount'], $prev);
        }

        $palette = ['#10B981', '#3B82F6', '#8B5CF6', '#F97316', '#EF4444', '#14B8A6', '#6B7280', '#EC4899'];
        $breakdown = fn (Collection $rows) => $rows->values()->map(fn ($r, $i) => [
            'label' => $r['name'], 'amount' => number_format((float) $r['amount']), 'value' => (float) $r['amount'], 'color' => $palette[$i % count($palette)],
        ])->all();

        $largestExpense = $expenses->sortByDesc('amount')->first();
        $insights = array_values(array_filter([
            ['icon' => $net >= $netPrev ? 'fa-arrow-trend-up' : 'fa-arrow-trend-down', 'color' => $net >= $netPrev ? 'green' : 'red',
                'text' => 'Net surplus '.($net >= $netPrev ? 'grew' : 'fell').' '.$pct($net, $netPrev).'% compared to the previous period.'],
            ['icon' => 'fa-percent', 'color' => 'blue', 'text' => "Surplus margin is {$margin}% (previous period {$marginPrev}%)."],
            $largestExpense ? ['icon' => 'fa-triangle-exclamation', 'color' => 'orange', 'text' => "{$largestExpense['name']} is the largest cost head this period."] : null,
        ]));

        return [
            'period' => ['from' => $from->toDateString(), 'to' => $to->toDateString(), 'compare_from' => $compareFrom->toDateString(), 'compare_to' => $compareTo->toDateString()],
            'kpis' => [
                'income' => ['value' => number_format($totalIncome, 2), 'change' => $pct($totalIncome, $totalIncomePrev).'%', 'dir' => $dir($totalIncome, $totalIncomePrev)],
                'expenses' => ['value' => number_format($totalExpenses, 2), 'change' => $pct($totalExpenses, $totalExpensesPrev).'%', 'dir' => $dir($totalExpenses, $totalExpensesPrev)],
                'net_profit' => ['value' => number_format($net, 2), 'change' => $pct($net, $netPrev).'%', 'dir' => $dir($net, $netPrev)],
                'margin' => ['value' => $margin.'%', 'change' => round($margin - $marginPrev, 1).'%', 'dir' => $dir($margin, $marginPrev)],
            ],
            'summary' => [
                'total_income' => number_format($totalIncome, 2),
                'total_expenses' => number_format($totalExpenses, 2),
                'net_profit' => number_format($net, 2),
                'margin' => $margin.'%',
            ],
            'chart' => $this->cumulativeSeries($society, $from, $to),
            'breakdown' => [
                'center_value' => '&#8377; '.number_format($totalIncome),
                'center_label' => 'Total Income',
                'income' => $breakdown($income),
                'expenses' => $breakdown($expenses),
            ],
            'insights' => $insights,
            'income_rows' => $incomeRows,
            'total_income' => [number_format($totalIncome, 2), number_format($totalIncomePrev, 2), number_format($totalIncome - $totalIncomePrev, 2), $pct($totalIncome, $totalIncomePrev).'%', $dir($totalIncome, $totalIncomePrev)],
            'expense_rows' => $expenseRows,
            'total_expenses' => [number_format($totalExpenses, 2), number_format($totalExpensesPrev, 2), number_format($totalExpenses - $totalExpensesPrev, 2), $pct($totalExpenses, $totalExpensesPrev).'%', $dir($totalExpenses, $totalExpensesPrev)],
            'net_profit_row' => [number_format($net, 2), number_format($netPrev, 2), number_format($net - $netPrev, 2), $pct($net, $netPrev).'%', $dir($net, $netPrev)],
            'net' => $net,
            'total_income_value' => $totalIncome,
            'total_expenses_value' => $totalExpenses,
        ];
    }

    /**
     * Balance sheet as on a date with a comparison date. The current-year
     * surplus (income − expenses to date) is shown under equity so that
     * assets = liabilities + equity.
     *
     * @return array<string, mixed>
     */
    public function balanceSheet(Society $society, Carbon $asOn, ?Carbon $compareOn = null): array
    {
        $compareOn ??= $asOn->copy()->subYear();

        $section = function (string $kind, Carbon $date) use ($society): Collection {
            return $this->detailAccounts($society)
                ->filter(fn (Account $a) => ($a->group?->kind ?? $this->kindForGroupName((string) $a->group?->name)) === $kind)
                ->map(fn (Account $a) => ['name' => $a->name, 'code' => $a->code, 'amount' => $this->balance($a, $date)])
                ->filter(fn ($r) => abs($r['amount']) > 0.005)
                ->values();
        };

        $assets = $section('asset', $asOn);
        $assetsPrev = $section('asset', $compareOn);
        $liabilities = $section('liability', $asOn);
        $liabilitiesPrev = $section('liability', $compareOn);
        $equity = $section('equity', $asOn);
        $equityPrev = $section('equity', $compareOn);

        $surplus = $this->surplusToDate($society, $asOn);
        $surplusPrev = $this->surplusToDate($society, $compareOn);

        $totalAssets = round($assets->sum('amount'), 2);
        $totalAssetsPrev = round($assetsPrev->sum('amount'), 2);
        $totalLiabilities = round($liabilities->sum('amount'), 2);
        $totalLiabilitiesPrev = round($liabilitiesPrev->sum('amount'), 2);
        $totalEquity = round($equity->sum('amount') + $surplus, 2);
        $totalEquityPrev = round($equityPrev->sum('amount') + $surplusPrev, 2);

        $pair = function (Collection $now, Collection $prev): array {
            $names = $now->pluck('name')->merge($prev->pluck('name'))->unique();

            return $names->map(fn ($name) => [
                $name,
                number_format((float) ($now->firstWhere('name', $name)['amount'] ?? 0), 2),
                number_format((float) ($prev->firstWhere('name', $name)['amount'] ?? 0), 2),
            ])->values()->all();
        };

        $change = fn (float $now, float $then) => '₹'.number_format(abs($now - $then)).' ('.($then != 0 ? round(abs($now - $then) / abs($then) * 100, 2) : 0).'%)';

        $direction = fn (float $now, float $then): string => abs($now - $then) < 0.005 ? 'flat' : ($now > $then ? 'up' : 'down');

        return [
            'as_on' => $asOn->toDateString(),
            'compare_on' => $compareOn->toDateString(),
            'stats' => [
                'total_assets' => number_format($totalAssets, 2),
                'total_liabilities' => number_format($totalLiabilities, 2),
                'total_equity' => number_format($totalEquity, 2),
                'bs_total' => number_format($totalLiabilities + $totalEquity, 2),
            ],
            'assets' => [
                ['group' => '1. Assets', 'rows' => $pair($assets, $assetsPrev)],
            ],
            'total_assets_row' => [number_format($totalAssets, 2), number_format($totalAssetsPrev, 2)],
            'liabilities' => [
                ['group' => '1. Liabilities', 'color' => 'red', 'rows' => $pair($liabilities, $liabilitiesPrev)],
                ['group' => '2. Equity & Surplus', 'color' => 'orange', 'rows' => array_merge(
                    $pair($equity, $equityPrev),
                    [['Surplus for the year', number_format($surplus, 2), number_format($surplusPrev, 2)]],
                )],
            ],
            'total_liab_row' => [number_format($totalLiabilities + $totalEquity, 2), number_format($totalLiabilitiesPrev + $totalEquityPrev, 2)],
            'insights' => [
                ['label' => 'Total Assets', 'change' => $change($totalAssets, $totalAssetsPrev), 'dir' => $direction($totalAssets, $totalAssetsPrev)],
                ['label' => 'Total Liabilities', 'change' => $change($totalLiabilities, $totalLiabilitiesPrev), 'dir' => $direction($totalLiabilities, $totalLiabilitiesPrev)],
                ['label' => 'Equity & Surplus', 'change' => $change($totalEquity, $totalEquityPrev), 'dir' => $direction($totalEquity, $totalEquityPrev)],
            ],
            'balanced' => abs($totalAssets - ($totalLiabilities + $totalEquity)) < 0.01,
            'total_assets_value' => $totalAssets,
            'surplus' => $surplus,
        ];
    }

    /**
     * Income − expenses booked up to a date (all time, since opening balances
     * already carry prior-year surplus through equity).
     */
    public function surplusToDate(Society $society, Carbon $asOn): float
    {
        $income = $this->detailAccounts($society)
            ->filter(fn (Account $a) => ($a->group?->kind ?? '') === 'income')
            ->sum(fn (Account $a) => $this->balance($a, $asOn));
        $expense = $this->detailAccounts($society)
            ->filter(fn (Account $a) => ($a->group?->kind ?? '') === 'expense')
            ->sum(fn (Account $a) => $this->balance($a, $asOn));

        return round($income - $expense, 2);
    }

    /* ----------------------------------------------------------------------
     |  Dashboard / rail figures
     |---------------------------------------------------------------------- */

    /**
     * @return array<string, string>
     */
    public function dashboardStats(Society $society): array
    {
        $today = Carbon::today();
        $fyStart = $this->financialYearStart($today);
        $cashAndBank = $this->detailAccounts($society)
            ->filter(fn (Account $a) => $a->is_bank || in_array($a->system_key, ['cash', 'bank'], true))
            ->sum(fn (Account $a) => $this->balance($a));

        return [
            'total_balance' => number_format($cashAndBank, 2),
            'total_income' => number_format($this->periodMovements($society, 'income', $fyStart, $today)->sum('amount'), 2),
            'total_expenses' => number_format($this->periodMovements($society, 'expense', $fyStart, $today)->sum('amount'), 2),
            'total_receivables' => number_format($this->balance($this->defaultAccount($society, 'receivables')) + $this->openBillsOutstanding($society), 2),
            'total_payables' => number_format($this->balance($this->defaultAccount($society, 'payables')) + $this->unpaidExpenses($society), 2),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public function accountBalanceSummary(Society $society): array
    {
        $totals = ['asset' => 0.0, 'liability' => 0.0, 'income' => 0.0, 'expense' => 0.0];
        foreach ($this->detailAccounts($society) as $account) {
            $kind = $account->group?->kind ?? $this->kindForGroupName((string) $account->group?->name);
            if (array_key_exists($kind, $totals)) {
                $totals[$kind] += max(0, $this->balance($account));
            }
        }
        $net = $totals['asset'] - $totals['liability'];

        return [
            'center_value' => '&#8377; '.number_format($net, 2),
            'center_label' => 'Net Position',
            'segments' => [
                ['label' => 'Assets', 'amount' => '&#8377; '.number_format($totals['asset']), 'value' => $totals['asset'], 'color' => '#10B981'],
                ['label' => 'Liabilities', 'amount' => '&#8377; '.number_format($totals['liability']), 'value' => $totals['liability'], 'color' => '#EF4444'],
                ['label' => 'Income', 'amount' => '&#8377; '.number_format($totals['income']), 'value' => $totals['income'], 'color' => '#8B5CF6'],
                ['label' => 'Expenses', 'amount' => '&#8377; '.number_format($totals['expense']), 'value' => $totals['expense'], 'color' => '#F97316'],
            ],
        ];
    }

    /**
     * Income vs expense for the last 12 months.
     *
     * @return array{labels: array<int, string>, income: array<int, float>, expense: array<int, float>, max: float}
     */
    public function cashFlowSeries(Society $society): array
    {
        $labels = [];
        $income = [];
        $expense = [];
        for ($i = 11; $i >= 0; $i--) {
            $month = Carbon::today()->subMonthsNoOverflow($i);
            $from = $month->copy()->startOfMonth();
            $to = $month->copy()->endOfMonth();
            $labels[] = $month->format('M');
            $income[] = round($this->periodMovements($society, 'income', $from, $to)->sum('amount'), 2);
            $expense[] = round($this->periodMovements($society, 'expense', $from, $to)->sum('amount'), 2);
        }

        return ['labels' => $labels, 'income' => $income, 'expense' => $expense, 'max' => $this->niceCeil(max(array_merge($income, $expense, [1])))];
    }

    /**
     * @return array<string, mixed>
     */
    public function transactionSummary(Society $society): array
    {
        $receipts = (float) Transaction::query()->forSociety($society)->where('type', 'receipt')->sum('debit');
        $payments = (float) Transaction::query()->forSociety($society)->where('type', 'payment')->sum('credit');
        $total = $receipts + $payments;
        $pct = fn (float $v) => ($total > 0 ? round($v / $total * 100, 2) : 0).'%';

        return [
            'center_value' => '&#8377; '.number_format($total),
            'center_label' => 'Total',
            'segments' => [
                ['label' => 'Receipts', 'amount' => '&#8377; '.number_format($receipts), 'pct' => $pct($receipts), 'value' => $receipts, 'color' => '#10B981'],
                ['label' => 'Payments', 'amount' => '&#8377; '.number_format($payments), 'pct' => $pct($payments), 'value' => $payments, 'color' => '#EF4444'],
            ],
        ];
    }

    /**
     * Bank / cash accounts with live balances for the dashboard rail.
     *
     * @return array<int, array<string, string>>
     */
    public function bankAccounts(Society $society): array
    {
        $colors = ['blue', 'red', 'green', 'orange', 'purple'];

        return $this->detailAccounts($society)
            ->filter(fn (Account $a) => $a->is_bank || in_array($a->system_key, ['cash', 'bank'], true))
            ->values()
            ->map(fn (Account $a, int $i) => [
                'id' => $a->id,
                'name' => $a->name,
                'sub' => $a->code,
                'amount' => number_format($this->balance($a), 2),
                'color' => $colors[$i % count($colors)],
                'icon' => $a->system_key === 'cash' ? 'fa-money-bill-wave' : 'fa-building-columns',
            ])->all();
    }

    /* ----------------------------------------------------------------------
     |  Helpers
     |---------------------------------------------------------------------- */

    /**
     * Net movement per detail account of a kind within a period (natural sign).
     *
     * @return Collection<int, array{account_id: int, name: string, amount: float}>
     */
    public function periodMovements(Society $society, string $kind, Carbon $from, Carbon $to): Collection
    {
        return $this->detailAccounts($society)
            ->filter(fn (Account $a) => ($a->group?->kind ?? $this->kindForGroupName((string) $a->group?->name)) === $kind)
            ->map(fn (Account $a) => ['account_id' => $a->id, 'name' => $a->name, 'amount' => $this->balance($a, $to, $from, includeOpening: false)])
            ->filter(fn ($r) => abs($r['amount']) > 0.005)
            ->values();
    }

    /**
     * @return Collection<int, Account>
     */
    public function detailAccounts(Society $society): Collection
    {
        return Account::query()->forSociety($society)->with('group')->where('type', 'detail')->orderBy('code')->get();
    }

    public function kindForGroupName(string $name): string
    {
        return match (true) {
            str_contains($name, 'Liabilit') => 'liability',
            str_contains($name, 'Income') => 'income',
            str_contains($name, 'Expense') => 'expense',
            str_contains($name, 'Equity') => 'equity',
            default => 'asset',
        };
    }

    /**
     * Cumulative daily income / expense / surplus lines for the P&L chart.
     *
     * @return array{labels: array<int, string>, income: array<int, float>, expense: array<int, float>, profit: array<int, float>, max: float}
     */
    private function cumulativeSeries(Society $society, Carbon $from, Carbon $to): array
    {
        $incomeIds = $this->detailAccounts($society)->filter(fn (Account $a) => ($a->group?->kind ?? '') === 'income')->pluck('id');
        $expenseIds = $this->detailAccounts($society)->filter(fn (Account $a) => ($a->group?->kind ?? '') === 'expense')->pluck('id');

        $daily = Transaction::query()->forSociety($society)
            ->whereBetween('date', [$from->toDateString(), $to->toDateString()])
            ->whereIn('account_id', $incomeIds->merge($expenseIds))
            ->selectRaw('date, account_id, SUM(credit) as c, SUM(debit) as d')
            ->groupBy('date', 'account_id')
            ->get();

        $points = 7;
        $span = max(1, (int) $from->diffInDays($to));
        $labels = [];
        $income = [];
        $expense = [];
        $profit = [];
        for ($i = 0; $i < $points; $i++) {
            $cut = $from->copy()->addDays((int) round($span * $i / ($points - 1)));
            $inc = (float) $daily->filter(fn ($r) => $incomeIds->contains($r->account_id) && Carbon::parse($r->date)->lte($cut))->sum(fn ($r) => $r->c - $r->d);
            $exp = (float) $daily->filter(fn ($r) => $expenseIds->contains($r->account_id) && Carbon::parse($r->date)->lte($cut))->sum(fn ($r) => $r->d - $r->c);
            $labels[] = $cut->format('d M');
            $income[] = round($inc, 2);
            $expense[] = round($exp, 2);
            $profit[] = round($inc - $exp, 2);
        }

        return ['labels' => $labels, 'income' => $income, 'expense' => $expense, 'profit' => $profit, 'max' => $this->niceCeil(max(array_merge($income, $expense, [1])))];
    }

    private function openBillsOutstanding(Society $society): float
    {
        return round((float) DB::table('maintenance_bills')
            ->where('society_id', $society->id)
            ->whereNull('deleted_at')
            ->whereIn('status', ['pending', 'partial', 'overdue'])
            ->sum('outstanding_amount'), 2);
    }

    private function unpaidExpenses(Society $society): float
    {
        return round((float) DB::table('expenses')
            ->where('society_id', $society->id)
            ->whereIn('payment_status', ['pending', 'overdue'])
            ->sum('due_amount'), 2);
    }

    private function niceCeil(float $value): float
    {
        if ($value <= 0) {
            return 1;
        }
        $magnitude = 10 ** floor(log10($value));
        foreach ([1, 2, 2.5, 5, 10] as $factor) {
            if ($value <= $factor * $magnitude) {
                return $factor * $magnitude;
            }
        }

        return 10 * $magnitude;
    }
}
