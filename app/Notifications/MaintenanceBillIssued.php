<?php

namespace App\Notifications;

use App\Models\MaintenanceBill;
use App\Services\BillDocumentService;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

/**
 * Bill delivery to a member (on-demand notifiable: email and/or mobile).
 * The event key selects the channels + template configured on the society's
 * Bill Settings → Notifications page.
 */
class MaintenanceBillIssued extends Notification
{
    use Queueable;

    public function __construct(
        public readonly MaintenanceBill $bill,
        public readonly string $event = 'bill_generated',
        public readonly bool $attachPdf = true,
    ) {}

    /**
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        $channels = app(BillDocumentService::class)->design($this->bill->society_id)->notificationEvent($this->event)['channels'];
        $via = [];

        if (($channels['email'] ?? false) && $notifiable->routeNotificationFor('mail', $this)) {
            $via[] = 'mail';
        }
        if ((($channels['sms'] ?? false) || ($channels['whatsapp'] ?? false)) && $notifiable->routeNotificationFor('sms', $this)) {
            $via[] = 'sms';
        }

        return $via;
    }

    public function toMail(object $notifiable): MailMessage
    {
        $bill = $this->bill;
        $mail = (new MailMessage)
            ->subject("{$this->subjectPrefix()} {$bill->bill_number} · {$bill->bill_month}")
            ->greeting('Hello '.($bill->member_name ?: 'Member').',')
            ->line($this->renderTemplate())
            ->line('Total payable: ₹ '.number_format((float) $bill->total_amount, 2).' · Due '.$bill->due_date?->format('d M Y').'.');

        if ($this->attachPdf) {
            $documents = app(BillDocumentService::class);
            $mail->attachData($documents->renderPdf($bill)->output(), $documents->fileName($bill), ['mime' => 'application/pdf']);
        }

        return $mail;
    }

    public function toSms(object $notifiable): string
    {
        return $this->renderTemplate();
    }

    /**
     * Fill the configured template's {tags} with bill values.
     */
    public function renderTemplate(): string
    {
        $template = app(BillDocumentService::class)->design($this->bill->society_id)->notificationEvent($this->event)['template'];
        $amount = $this->event === 'payment_received'
            ? (float) $this->bill->collected_amount
            : ((float) $this->bill->outstanding_amount ?: (float) $this->bill->total_amount);

        return strtr($template, [
            '{member_name}' => $this->bill->member_name ?: 'Member',
            '{bill_no}' => $this->bill->bill_number,
            '{amount}' => '₹ '.number_format($amount, 2),
            '{bill_month}' => $this->bill->bill_month,
            '{due_date}' => $this->bill->due_date?->format('d M Y') ?? '',
            '{society}' => $this->bill->society?->name ?? '',
        ]);
    }

    private function subjectPrefix(): string
    {
        return match ($this->event) {
            'payment_reminder' => 'Payment reminder for bill',
            'overdue_reminder' => 'Overdue: bill',
            'payment_received' => 'Payment received for bill',
            default => 'Maintenance bill',
        };
    }
}
