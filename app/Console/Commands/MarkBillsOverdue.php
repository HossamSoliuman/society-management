<?php

namespace App\Console\Commands;

use App\Services\BillingService;
use Illuminate\Console\Command;

class MarkBillsOverdue extends Command
{
    protected $signature = 'bills:mark-overdue';

    protected $description = 'Flip pending / partial maintenance bills past their due date to overdue';

    public function handle(BillingService $billing): int
    {
        $changed = $billing->markOverdue();
        $this->info("Marked {$changed} bill(s) overdue.");

        return self::SUCCESS;
    }
}
