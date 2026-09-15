<?php

namespace App\Console\Commands;

use App\Jobs\SendMaintenanceBill;
use App\Models\BillSetting;
use App\Models\MaintenanceBill;
use Illuminate\Console\Command;
use Illuminate\Support\Carbon;

class SendBillReminders extends Command
{
    protected $signature = 'bills:send-reminders';

    protected $description = 'Queue payment / overdue reminders for unpaid bills per each society reminder schedule';

    public function handle(): int
    {
        $today = Carbon::today();
        $queued = 0;

        BillSetting::query()->whereNotNull('society_id')->each(function (BillSetting $settings) use ($today, &$queued) {
            $dueDates = [];
            foreach ($settings->reminderDaysBeforeDue() as $days) {
                $dueDates['payment_reminder'][] = $today->copy()->addDays($days)->toDateString();
            }
            foreach ($settings->reminderDaysAfterDue() as $days) {
                $dueDates['overdue_reminder'][] = $today->copy()->subDays($days)->toDateString();
            }

            foreach ($dueDates as $event => $dates) {
                $channels = $settings->notificationEvent($event)['channels'];
                if (! ($channels['email'] || $channels['sms'] || $channels['whatsapp'])) {
                    continue;
                }

                MaintenanceBill::query()
                    ->where('society_id', $settings->society_id)
                    ->whereIn('status', ['pending', 'partial', 'overdue'])
                    ->where('outstanding_amount', '>', 0)
                    ->where(function ($q) use ($dates) {
                        foreach ($dates as $date) {
                            $q->orWhereDate('due_date', $date);
                        }
                    })
                    ->where(fn ($q) => $q->whereNull('last_reminder_at')->orWhereDate('last_reminder_at', '<', $today->toDateString()))
                    ->each(function (MaintenanceBill $bill) use ($event, $channels, &$queued) {
                        $bill->forceFill([
                            'send_email' => (bool) $channels['email'],
                            'send_sms' => (bool) ($channels['sms'] || $channels['whatsapp']),
                        ])->saveQuietly();
                        SendMaintenanceBill::dispatch($bill, $event);
                        $queued++;
                    });
            }
        });

        $this->info("Queued {$queued} reminder(s).");

        return self::SUCCESS;
    }
}
