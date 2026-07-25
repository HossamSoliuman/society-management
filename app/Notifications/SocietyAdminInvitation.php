<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class SocietyAdminInvitation extends Notification
{
    use Queueable;

    public function __construct(private readonly string $token, private readonly ?string $societyName = null) {}

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $url = route('password.reset', ['token' => $this->token, 'email' => $notifiable->email]);

        return (new MailMessage)
            ->subject('Set up your Society Management account')
            ->greeting("Hello {$notifiable->name},")
            ->line($this->societyName
                ? "You have been invited to manage {$this->societyName}."
                : 'You have been invited to the Society Management platform.')
            ->line('Create your password using the secure link below. This link expires in 60 minutes.')
            ->action('Create password', $url)
            ->line('If you were not expecting this invitation, no action is required.');
    }
}
