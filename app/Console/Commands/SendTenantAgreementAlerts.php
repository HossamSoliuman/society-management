<?php

namespace App\Console\Commands;

use App\Models\TenantAgreement;
use App\Models\User;
use App\Notifications\TenantAgreementExpiryAlert;
use Illuminate\Console\Command;
use Illuminate\Support\Carbon;

/**
 * Alerts society admins and managers 30 and 7 days before a running rental
 * agreement ends, and on its last day.
 */
class SendTenantAgreementAlerts extends Command
{
    protected $signature = 'tenants:send-agreement-alerts';

    protected $description = 'Notify society admins about rental agreements that are about to end';

    /** @var array<int, int> */
    public const ALERT_DAYS = [30, 7, 0];

    public function handle(): int
    {
        $today = Carbon::today();
        $sent = 0;

        foreach (self::ALERT_DAYS as $days) {
            TenantAgreement::query()
                ->with(['tenant', 'unit'])
                ->where('status', 'active')
                ->whereHas('tenant', fn ($q) => $q->where('status', 'active'))
                ->whereDate('end_date', $today->copy()->addDays($days)->toDateString())
                ->each(function (TenantAgreement $agreement) use ($days, &$sent) {
                    $admins = User::query()
                        ->where('society_id', $agreement->society_id)
                        ->where('status', 'active')
                        ->whereHas('roles', fn ($q) => $q->whereIn('name', ['society_admin', 'manager']))
                        ->get();

                    foreach ($admins as $admin) {
                        $admin->notify(new TenantAgreementExpiryAlert($agreement, $days));
                    }
                    $sent += $admins->isNotEmpty() ? 1 : 0;
                });
        }

        $this->info("Sent alerts for {$sent} agreement(s).");

        return self::SUCCESS;
    }
}
