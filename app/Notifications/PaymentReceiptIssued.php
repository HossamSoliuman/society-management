<?php

namespace App\Notifications;

use App\Models\CollectionPayment;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

/**
 * Emails a payment receipt (PDF attached) to the member (on-demand notifiable).
 */
class PaymentReceiptIssued extends Notification
{
    use Queueable;

    public function __construct(public readonly CollectionPayment $payment) {}

    /**
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $payment = $this->payment->loadMissing(['society', 'maintenanceBill']);

        $pdf = Pdf::loadView('society.collections.receipts.pdf', [
            'payment' => $payment,
            'society' => $payment->society,
        ])->setPaper('a4')->output();

        return (new MailMessage)
            ->subject("Payment receipt {$payment->receipt_number} · ₹ ".number_format((float) $payment->paid_amount, 2))
            ->greeting('Hello '.($payment->member_name ?: 'Member').',')
            ->line('We have received your payment of ₹ '.number_format((float) $payment->paid_amount, 2).' on '.$payment->receipt_date?->format('d M Y').'.')
            ->line('Your receipt is attached.')
            ->attachData($pdf, str_replace(['/', '\\'], '-', $payment->receipt_number).'.pdf', ['mime' => 'application/pdf']);
    }
}
