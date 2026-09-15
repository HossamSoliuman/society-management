<?php

namespace App\Imports;

use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Throwable;

/**
 * Parses a bank statement (CSV/XLSX) into normalised rows. Accepts the common
 * Indian bank exports: Date, Description/Narration, Ref No/Cheque No,
 * Debit/Withdrawal, Credit/Deposit, Balance.
 */
class BankStatementImport implements ToCollection, WithHeadingRow
{
    /** @var array<int, array{statement_date: string, description: ?string, reference: ?string, debit: float, credit: float, balance: ?float}> */
    public array $rows = [];

    /** @var array<int, array{row: int, error: string}> */
    public array $errors = [];

    public function collection(Collection $rows): void
    {
        foreach ($rows as $index => $row) {
            $row = collect($row)->mapWithKeys(fn ($v, $k) => [strtolower(trim((string) $k)) => $v]);
            $line = $index + 2;

            $date = $this->normaliseDate($this->pick($row, ['date', 'txn_date', 'transaction_date', 'value_date']));
            $debit = $this->number($this->pick($row, ['debit', 'withdrawal', 'withdrawal_amt', 'dr', 'debit_amount']));
            $credit = $this->number($this->pick($row, ['credit', 'deposit', 'deposit_amt', 'cr', 'credit_amount']));
            $amount = $this->number($this->pick($row, ['amount']));
            if ($amount !== null && $debit === null && $credit === null) {
                $debit = $amount < 0 ? abs($amount) : null;
                $credit = $amount > 0 ? $amount : null;
            }

            if (! $date && $debit === null && $credit === null) {
                continue;
            }
            if (! $date) {
                $this->errors[] = ['row' => $line, 'error' => 'Unreadable date.'];

                continue;
            }
            if (($debit ?? 0) <= 0 && ($credit ?? 0) <= 0) {
                $this->errors[] = ['row' => $line, 'error' => 'No debit or credit amount.'];

                continue;
            }

            $this->rows[] = [
                'statement_date' => $date,
                'description' => trim((string) $this->pick($row, ['description', 'narration', 'particulars', 'details'])) ?: null,
                'reference' => trim((string) $this->pick($row, ['ref_no', 'reference', 'reference_no', 'cheque_no', 'chq_no', 'utr'])) ?: null,
                'debit' => round((float) ($debit ?? 0), 2),
                'credit' => round((float) ($credit ?? 0), 2),
                'balance' => $this->number($this->pick($row, ['balance', 'closing_balance'])),
            ];
        }
    }

    private function pick(Collection $row, array $keys): mixed
    {
        foreach ($keys as $key) {
            if ($row->has($key) && $row[$key] !== null && $row[$key] !== '') {
                return $row[$key];
            }
        }

        return null;
    }

    private function number(mixed $value): ?float
    {
        if ($value === null || $value === '') {
            return null;
        }
        $clean = preg_replace('/[^0-9.\-]/', '', (string) $value);

        return is_numeric($clean) ? (float) $clean : null;
    }

    private function normaliseDate(mixed $value): ?string
    {
        if ($value === null || $value === '') {
            return null;
        }
        try {
            if (is_numeric($value)) {
                return Carbon::createFromDate(1899, 12, 30)->addDays((int) $value)->toDateString();
            }
            $value = trim((string) $value);
            if (preg_match('#^(\d{1,2})[/-](\d{1,2})[/-](\d{2,4})$#', $value, $m)) {
                $year = strlen($m[3]) === 2 ? 2000 + (int) $m[3] : (int) $m[3];

                return Carbon::createFromDate($year, (int) $m[2], (int) $m[1])->toDateString();
            }

            return Carbon::parse($value)->toDateString();
        } catch (Throwable) {
            return null;
        }
    }
}
