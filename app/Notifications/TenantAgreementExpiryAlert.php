<?php

namespace App\Notifications;

use App\Models\TenantAgreement;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class TenantAgreementExpiryAlert extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(public readonly TenantAgreement $agreement, public readonly int $daysLeft) {}

    /**
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['mail', 'database'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $a = $this->agreement;

        return (new MailMessage)
            ->subject('Rental agreement '.$this->when().': '.($a->tenant?->name ?? 'tenant').($a->unit ? " ({$a->unit->unit_number})" : ''))
            ->greeting("Hello {$notifiable->name},")
            ->line('The rental agreement for '.($a->tenant?->name ?? 'a tenant').($a->unit ? " in {$a->unit->unit_number}" : '')." {$this->when()} (end date {$a->end_date->format('d M Y')}).")
            ->line('Ask the owner for a renewed agreement or record the move-out.')
            ->action('Open tenant', route('society.tenants.show', $a->tenant_id));
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'type' => 'agreement_expiry',
            'title' => 'Agreement for '.($this->agreement->tenant?->name ?? 'a tenant').' '.$this->when(),
            'agreement_id' => $this->agreement->id,
            'url' => route('society.tenants.show', $this->agreement->tenant_id),
        ];
    }

    private function when(): string
    {
        return $this->daysLeft <= 0 ? 'ends today' : "ends in {$this->daysLeft} days";
    }
}
