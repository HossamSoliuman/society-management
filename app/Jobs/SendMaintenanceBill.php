<?php

namespace App\Jobs;

use App\Models\MaintenanceBill;
use App\Notifications\MaintenanceBillIssued;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Notification;

/**
 * Email (with PDF) and/or SMS a bill to its member, honouring the bill's
 * send_* flags and the society's notification settings.
 */
class SendMaintenanceBill implements ShouldQueue
{
    use Queueable;

    public function __construct(public readonly MaintenanceBill $bill, public readonly string $event = 'bill_generated') {}

    public function handle(): void
    {
        $bill = $this->bill->fresh(['member', 'unit', 'society']);
        if (! $bill) {
            return;
        }

        $email = $bill->member?->email;
        $mobile = $bill->member?->mobile ?: $bill->unit?->owner_mobile;

        $routes = [];
        if ($bill->send_email && $email) {
            $routes['mail'] = $email;
        }
        if (($bill->send_sms || $bill->send_whatsapp) && $mobile) {
            $routes['sms'] = $mobile;
        }

        if ($routes === []) {
            return;
        }

        Notification::routes($routes)->notify(new MaintenanceBillIssued($bill, $this->event));

        $update = ['delivered_at' => now()];
        if ($this->event !== 'bill_generated') {
            $update = ['last_reminder_at' => now(), 'reminders_sent' => $bill->reminders_sent + 1];
        }
        $bill->forceFill($update)->saveQuietly();
    }
}
