<?php

namespace App\Notifications;

use App\Models\AmcContract;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class AmcExpiryAlert extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(public readonly AmcContract $contract, public readonly int $daysLeft) {}

    /**
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['mail', 'database'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $c = $this->contract;
        $when = $this->daysLeft <= 0 ? 'has expired' : "expires in {$this->daysLeft} day(s)";

        return (new MailMessage)
            ->subject("AMC {$when}: {$c->item_asset}")
            ->greeting("Hello {$notifiable->name},")
            ->line("The AMC for {$c->item_asset}".($c->vendor_name ? " with {$c->vendor_name}" : '')." {$when} (end date {$c->end_date?->format('d M Y')}).")
            ->line('Contract value: ₹ '.number_format((float) $c->amount, 2).($c->contract_no ? " · Contract no. {$c->contract_no}" : ''))
            ->action('Open AMC tracker', route('society.amc.index'));
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'type' => 'amc_expiry',
            'title' => "AMC for {$this->contract->item_asset} ".($this->daysLeft <= 0 ? 'has expired' : "expires in {$this->daysLeft} days"),
            'contract_id' => $this->contract->id,
            'url' => route('society.amc.index'),
        ];
    }
}
