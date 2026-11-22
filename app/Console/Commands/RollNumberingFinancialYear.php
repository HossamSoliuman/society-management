<?php

namespace App\Console\Commands;

use App\Models\NumberingSeries;
use App\Services\AccountingService;
use Illuminate\Console\Command;
use Illuminate\Support\Carbon;

class RollNumberingFinancialYear extends Command
{
    protected $signature = 'numbering:roll-financial-year';

    protected $description = 'Move yearly society numbering series into the current financial year and restart their sequence';

    public function handle(AccountingService $accounting): int
    {
        $rolled = 0;

        NumberingSeries::query()
            ->with('society')
            ->whereNotNull('society_id')
            ->where('reset_frequency', 'yearly')
            ->each(function (NumberingSeries $series) use ($accounting, &$rolled) {
                $start = $accounting->financialYearStart(Carbon::today(), $series->society);
                $label = $accounting->financialYearLabel(Carbon::today(), $series->society);

                if ($series->financial_year === $label) {
                    return;
                }

                // Only restart at 1 when the number carries the year, so numbers stay unique.
                $restart = str_contains((string) $series->format, 'YY');

                $series->forceFill([
                    'financial_year' => $label,
                    'start_date' => $start,
                    'next_number' => $restart ? 1 : $series->next_number,
                ])->save();

                $rolled++;
            });

        $this->info("Rolled {$rolled} numbering series into the current financial year.");

        return self::SUCCESS;
    }
}
