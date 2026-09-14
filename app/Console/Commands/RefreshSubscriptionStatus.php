<?php

namespace App\Console\Commands;

use App\Services\SubscriptionService;
use Illuminate\Console\Command;

class RefreshSubscriptionStatus extends Command
{
    protected $signature = 'subscriptions:refresh-status';

    protected $description = 'Flag subscriptions as expiring_soon / expired and cascade the status to each society';

    public function handle(SubscriptionService $subscriptions): int
    {
        $changed = $subscriptions->refreshStatuses();

        $this->info("Updated {$changed} subscription(s).");

        return self::SUCCESS;
    }
}
