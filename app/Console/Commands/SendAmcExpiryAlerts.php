<?php

namespace App\Console\Commands;

use App\Models\AmcContract;
use App\Models\User;
use App\Notifications\AmcExpiryAlert;
use Illuminate\Console\Command;
use Illuminate\Support\Carbon;

/**
 * Refreshes AMC statuses from their end dates and emails society admins at
 * 30 and 7 days before expiry (and on the day a contract lapses).
 */
class SendAmcExpiryAlerts extends Command
{
    protected $signature = 'amc:send-expiry-alerts';

    protected $description = 'Refresh AMC contract statuses and email society admins about upcoming expiries';

    /** @var array<int, int> */
    public const ALERT_DAYS = [30, 7, 0];

    public function handle(): int
    {
        $today = Carbon::today();
        $refreshed = $this->refreshStatuses($today);

        $sent = 0;
        foreach (self::ALERT_DAYS as $days) {
            $contracts = AmcContract::query()
                ->whereNotNull('society_id')
                ->where('status', '!=', 'draft')
                ->whereDate('end_date', $today->copy()->addDays($days)->toDateString())
                ->get();

            foreach ($contracts as $contract) {
                $admins = User::query()
                    ->where('society_id', $contract->society_id)
                    ->where('status', 'active')
                    ->whereHas('roles', fn ($q) => $q->whereIn('name', ['society_admin', 'manager']))
                    ->get();

                foreach ($admins as $admin) {
                    $admin->notify(new AmcExpiryAlert($contract, $days));
                }
                if ($admins->isNotEmpty()) {
                    $sent++;
                }
            }
        }

        $this->info("Refreshed {$refreshed} status(es); sent alerts for {$sent} contract(s).");

        return self::SUCCESS;
    }

    private function refreshStatuses(Carbon $today): int
    {
        $changed = 0;

        AmcContract::query()->whereNotNull('end_date')->where('status', '!=', 'draft')->each(function (AmcContract $contract) use ($today, &$changed) {
            $end = $contract->end_date->copy()->startOfDay();
            $status = match (true) {
                $end->lt($today) => 'expired',
                $today->diffInDays($end) <= 30 => 'expiring_soon',
                default => 'active',
            };
            if ($status !== $contract->status) {
                $contract->forceFill(['status' => $status])->save();
                $changed++;
            }
        });

        return $changed;
    }
}
