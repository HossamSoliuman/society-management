<?php

namespace App\Notifications;

use App\Models\Invoice;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class PlatformInvoiceReminder extends Notification implements ShouldQueue
{
    use Queueable;

    public bool $deleteWhenMissingModels = true;

    public function __construct(public readonly Invoice $invoice) {}

    /**
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['mail', 'database'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $invoice = $this->invoice;
        $overdue = $invoice->due_date?->isPast();

        return (new MailMessage)
            ->subject("Payment reminder: invoice {$invoice->invoice_number}")
            ->greeting("Hello {$notifiable->name},")
            ->line("Invoice {$invoice->invoice_number} for {$invoice->society?->name} has ".format_inr($invoice->outstanding_amount, 2).' outstanding.')
            ->line($overdue
                ? "It was due on {$invoice->due_date->format('d M Y')}. Please pay it to keep your access uninterrupted."
                : "It is due on {$invoice->due_date?->format('d M Y')}.")
            ->action('View subscription', route('society.subscription.index'));
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'type' => 'subscription_renewal',
            'title' => "Invoice {$this->invoice->invoice_number} has ".format_inr($this->invoice->outstanding_amount, 2).' due',
            'invoice_id' => $this->invoice->id,
            'url' => route('society.subscription.index'),
        ];
    }
}
