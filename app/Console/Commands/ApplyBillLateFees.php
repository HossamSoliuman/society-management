<?php

namespace App\Console\Commands;

use App\Services\BillingService;
use Illuminate\Console\Command;

class ApplyBillLateFees extends Command
{
    protected $signature = 'bills:apply-late-fees';

    protected $description = 'Add the configured late fee to overdue bills once their grace period has passed';

    public function handle(BillingService $billing): int
    {
        $applied = $billing->applyLateFees();
        $this->info("Applied late fees to {$applied} bill(s).");

        return self::SUCCESS;
    }
}
