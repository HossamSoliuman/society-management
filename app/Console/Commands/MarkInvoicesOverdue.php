<?php

namespace App\Console\Commands;

use App\Services\PlatformBillingService;
use Illuminate\Console\Command;

class MarkInvoicesOverdue extends Command
{
    protected $signature = 'invoices:mark-overdue';

    protected $description = 'Flip pending platform invoices past their due date to overdue';

    public function handle(PlatformBillingService $billing): int
    {
        $changed = $billing->markOverdue();

        $this->info("Marked {$changed} invoice(s) overdue.");

        return self::SUCCESS;
    }
}
