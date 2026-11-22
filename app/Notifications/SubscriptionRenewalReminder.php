<?php

namespace App\Notifications;

use App\Models\Subscription;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class SubscriptionRenewalReminder extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(public readonly Subscription $subscription, public readonly int $daysLeft) {}

    /**
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['mail', 'database'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $society = $this->subscription->society;
        $plan = $this->subscription->plan;
        $when = $this->whenLabel();

        return (new MailMessage)
            ->subject("Your {$society?->name} subscription {$when}")
            ->greeting("Hello {$notifiable->name},")
            ->line("The {$plan?->name} subscription for {$society?->name} ends on {$this->subscription->end_date->format('d M Y')}.")
            ->line("Access continues for {$society?->grace_period_days} grace days after that, then the society panel is locked.")
            ->action('View subscription', route('society.subscription.index'))
            ->line('Contact the platform team to renew or upgrade your plan.');
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'type' => 'subscription_renewal',
            'title' => 'Subscription '.$this->whenLabel(),
            'subscription_id' => $this->subscription->id,
            'end_date' => $this->subscription->end_date->toDateString(),
            'url' => route('society.subscription.index'),
        ];
    }

    private function whenLabel(): string
    {
        return match (true) {
            $this->daysLeft < 0 => 'has expired',
            $this->daysLeft === 0 => 'expires today',
            $this->daysLeft === 1 => 'expires tomorrow',
            default => "expires in {$this->daysLeft} days",
        };
    }
}
