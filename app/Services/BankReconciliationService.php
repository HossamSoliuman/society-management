<?php

namespace App\Services;

use App\Models\Account;
use App\Models\BankStatementLine;
use App\Models\Society;
use App\Models\Transaction;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

/**
 * Imports bank statement lines for a bank account and matches them against
 * that account's ledger transactions by amount + date (± tolerance) and,
 * when present, reference number.
 */
class BankReconciliationService
{
    public const DATE_TOLERANCE_DAYS = 3;

    /**
     * Store parsed statement rows and auto-match them. Returns the batch id.
     *
     * @param  array<int, array{statement_date: string, description: ?string, reference: ?string, debit: float, credit: float, balance: ?float}>  $rows
     * @return array{batch: string, imported: int, matched: int}
     */
    public function import(Society $society, Account $account, array $rows): array
    {
        $batch = Str::lower(Str::random(12));
        $matched = 0;

        DB::transaction(function () use ($society, $account, $rows, $batch, &$matched) {
            foreach ($rows as $row) {
                $line = BankStatementLine::create($row + [
                    'society_id' => $society->id,
                    'account_id' => $account->id,
                    'import_batch' => $batch,
                ]);

                if ($this->match($line)) {
                    $matched++;
                }
            }
        });

        return ['batch' => $batch, 'imported' => count($rows), 'matched' => $matched];
    }

    /**
     * Find an unreconciled ledger row on the same account with the same
     * amount/direction within the date tolerance (reference wins ties).
     */
    public function match(BankStatementLine $line): bool
    {
        $amount = $line->amount(); // + = money in (ledger debit), − = money out (ledger credit)
        $column = $amount >= 0 ? 'debit' : 'credit';

        $candidates = Transaction::query()
            ->where('account_id', $line->account_id)
            ->whereNull('reconciled_at')
            ->where($column, round(abs($amount), 2))
            ->whereBetween('date', [
                $line->statement_date->copy()->subDays(self::DATE_TOLERANCE_DAYS)->toDateString(),
                $line->statement_date->copy()->addDays(self::DATE_TOLERANCE_DAYS)->toDateString(),
            ])
            ->orderBy('date')
            ->get();

        if ($candidates->isEmpty()) {
            return false;
        }

        $transaction = null;
        if ($line->reference) {
            $transaction = $candidates->first(fn (Transaction $t) => $t->reference_no && Str::contains(strtolower($t->reference_no), strtolower($line->reference)));
        }
        $transaction ??= $candidates->first(fn (Transaction $t) => $t->date->isSameDay($line->statement_date)) ?? $candidates->first();

        $this->link($line, $transaction);

        return true;
    }

    public function link(BankStatementLine $line, Transaction $transaction): void
    {
        DB::transaction(function () use ($line, $transaction) {
            $transaction->forceFill(['reconciled_at' => now(), 'bank_statement_line_id' => $line->id])->save();
            $line->forceFill(['transaction_id' => $transaction->id, 'matched_at' => now()])->save();
        });
    }

    public function unlink(BankStatementLine $line): void
    {
        DB::transaction(function () use ($line) {
            if ($line->transaction) {
                $line->transaction->forceFill(['reconciled_at' => null, 'bank_statement_line_id' => null])->save();
            }
            $line->forceFill(['transaction_id' => null, 'matched_at' => null])->save();
        });
    }

    /**
     * Side-by-side rows for the reconciliation screen.
     *
     * @return array{rows: Collection<int, array<string, mixed>>, summary: array<string, mixed>}
     */
    public function overview(Society $society, Account $account, ?Carbon $from = null, ?Carbon $to = null): array
    {
        $lines = BankStatementLine::query()
            ->forSociety($society)
            ->where('account_id', $account->id)
            ->when($from, fn ($q) => $q->whereDate('statement_date', '>=', $from))
            ->when($to, fn ($q) => $q->whereDate('statement_date', '<=', $to))
            ->with('transaction')
            ->orderByDesc('statement_date')
            ->get();

        $unmatchedLedger = Transaction::query()
            ->forSociety($society)
            ->where('account_id', $account->id)
            ->whereNull('reconciled_at')
            ->when($from, fn ($q) => $q->whereDate('date', '>=', $from))
            ->when($to, fn ($q) => $q->whereDate('date', '<=', $to))
            ->orderByDesc('date')
            ->get();

        $rows = $lines->map(fn (BankStatementLine $line) => [
            'id' => $line->id,
            'date' => $line->statement_date->format('d M Y'),
            'description' => $line->description ?: ($line->transaction?->description ?? '—'),
            'reference' => $line->reference,
            'book' => $line->transaction ? number_format((float) ($line->transaction->debit ?: $line->transaction->credit), 2) : '0.00',
            'bank' => number_format(abs($line->amount()), 2),
            'matched' => $line->matched_at !== null,
            'transaction_id' => $line->transaction_id,
        ])->concat($unmatchedLedger->map(fn (Transaction $t) => [
            'id' => null,
            'date' => $t->date->format('d M Y'),
            'description' => $t->description ?: $t->reference_no ?: '—',
            'reference' => $t->reference_no,
            'book' => number_format((float) ($t->debit ?: $t->credit), 2),
            'bank' => '0.00',
            'matched' => false,
            'transaction_id' => $t->id,
        ]))->values();

        $bankBalance = $lines->sortByDesc('statement_date')->first()?->balance;

        return [
            'rows' => $rows,
            'summary' => [
                'statement_lines' => $lines->count(),
                'matched' => $lines->whereNotNull('matched_at')->count(),
                'unmatched_bank' => $lines->whereNull('matched_at')->count(),
                'unmatched_book' => $unmatchedLedger->count(),
                'bank_balance' => $bankBalance !== null ? number_format((float) $bankBalance, 2) : '—',
                'book_balance' => number_format(app(AccountingService::class)->balance($account), 2),
            ],
        ];
    }
}
