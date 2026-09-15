<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class MemberPortalInvitation extends Notification
{
    use Queueable;

    public function __construct(private readonly string $token, private readonly string $societyName) {}

    /**
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $url = route('password.reset', ['token' => $this->token, 'email' => $notifiable->email]);

        return (new MailMessage)
            ->subject("Your {$this->societyName} member portal account")
            ->greeting("Hello {$notifiable->name},")
            ->line("{$this->societyName} has invited you to the resident portal where you can view bills, pay online, download receipts and raise requests.")
            ->line('Create your password using the secure link below. This link expires in 60 minutes.')
            ->action('Create password', $url)
            ->line('If you were not expecting this invitation, no action is required.');
    }
}
