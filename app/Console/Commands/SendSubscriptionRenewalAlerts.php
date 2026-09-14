<?php

namespace App\Console\Commands;

use App\Services\SubscriptionService;
use Illuminate\Console\Command;

class SendSubscriptionRenewalAlerts extends Command
{
    protected $signature = 'subscriptions:send-renewal-alerts';

    protected $description = 'Email society admins whose subscription ends in 30, 7 or 1 days';

    public function handle(SubscriptionService $subscriptions): int
    {
        $sent = $subscriptions->sendRenewalAlerts();

        $this->info("Sent renewal alerts for {$sent} subscription(s).");

        return self::SUCCESS;
    }
}
