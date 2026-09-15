<?php

namespace App\Imports;

use App\Models\ChargeHead;
use App\Models\Society;
use App\Models\Unit;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Throwable;

/**
 * Parses the maintenance-bill upload template (see BulkUploadController@sample)
 * into normalised rows plus per-row validation errors for the review step.
 *
 * Expected headings: Flat No, Member Mobile, Bill Month, Bill Date, Due Date,
 * Charge Head, Amount, Notes.
 */
class MaintenanceBillImport implements ToCollection, WithHeadingRow
{
    /** @var array<int, array{row: int, flat_no: string, member_mobile: ?string, bill_month: string, bill_date: string, due_date: string, charge_head: string, amount: float, notes: ?string}> */
    public array $rows = [];

    /** @var array<int, array{row: int, errors: array<int, string>, data: array<string, mixed>}> */
    public array $errors = [];

    /** @var array<string, bool> */
    private array $knownUnits = [];

    /** @var array<string, bool> */
    private array $knownHeads = [];

    public function __construct(private readonly Society $society)
    {
        $this->knownUnits = Unit::query()->forSociety($society)->pluck('unit_number')
            ->mapWithKeys(fn ($n) => [strtoupper(trim((string) $n)) => true])->all();
        $this->knownHeads = ChargeHead::query()->forSociety($society)->where('status', 'active')->pluck('name')
            ->mapWithKeys(fn ($n) => [strtolower(trim((string) $n)) => true])->all();
    }

    public function collection(Collection $rows): void
    {
        foreach ($rows as $index => $row) {
            $line = $index + 2; // 1-based + heading row
            $data = [
                'row' => $line,
                'flat_no' => trim((string) ($row['flat_no'] ?? '')),
                'member_mobile' => trim((string) ($row['member_mobile'] ?? '')) ?: null,
                'bill_month' => trim((string) ($row['bill_month'] ?? '')),
                'bill_date' => $this->normaliseDate($row['bill_date'] ?? null),
                'due_date' => $this->normaliseDate($row['due_date'] ?? null),
                'charge_head' => trim((string) ($row['charge_head'] ?? '')),
                'amount' => is_numeric($row['amount'] ?? null) ? round((float) $row['amount'], 2) : null,
                'notes' => trim((string) ($row['notes'] ?? '')) ?: null,
            ];

            if ($data['flat_no'] === '' && $data['charge_head'] === '' && $data['amount'] === null) {
                continue; // blank line
            }

            $errors = [];
            if ($data['flat_no'] === '') {
                $errors[] = 'Flat No is required.';
            } elseif (! isset($this->knownUnits[strtoupper($data['flat_no'])])) {
                $errors[] = "Flat {$data['flat_no']} does not exist in this society.";
            }
            if ($data['bill_month'] === '') {
                $errors[] = 'Bill Month is required.';
            }
            if (! $data['bill_date']) {
                $errors[] = 'Bill Date is missing or unreadable.';
            }
            if (! $data['due_date']) {
                $errors[] = 'Due Date is missing or unreadable.';
            } elseif ($data['bill_date'] && $data['due_date'] < $data['bill_date']) {
                $errors[] = 'Due Date is before Bill Date.';
            }
            if ($data['charge_head'] === '') {
                $errors[] = 'Charge Head is required.';
            } elseif (! isset($this->knownHeads[strtolower($data['charge_head'])])) {
                $errors[] = "Charge head \"{$data['charge_head']}\" is not configured.";
            }
            if ($data['amount'] === null || $data['amount'] < 0) {
                $errors[] = 'Amount must be a number ≥ 0.';
            }

            if ($errors !== []) {
                $this->errors[] = ['row' => $line, 'errors' => $errors, 'data' => $data];
            } else {
                $this->rows[] = $data;
            }
        }
    }

    /**
     * Accept Excel serials, d/m/Y, Y-m-d, "01 Jun 2025", etc. → Y-m-d.
     */
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
            if (preg_match('#^(\d{1,2})/(\d{1,2})/(\d{4})$#', $value, $m)) {
                return Carbon::createFromDate((int) $m[3], (int) $m[2], (int) $m[1])->toDateString();
            }

            return Carbon::parse($value)->toDateString();
        } catch (Throwable) {
            return null;
        }
    }
}
