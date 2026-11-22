<?php

namespace App\Notifications;

use App\Models\DemoRequest;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class DemoRequestReceived extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(public readonly DemoRequest $demoRequest) {}

    /**
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['mail', 'database'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $lead = $this->demoRequest;

        return (new MailMessage)
            ->subject("New demo request: {$lead->society_name}, {$lead->city}")
            ->greeting("Hello {$notifiable->name},")
            ->line("{$lead->name} ({$lead->roleLabel()}) asked for a demo for {$lead->society_name}, {$lead->city}".($lead->units ? " with {$lead->units} units." : '.'))
            ->line("Phone: {$lead->phone} · Email: {$lead->email}")
            ->when($lead->message, fn (MailMessage $mail) => $mail->line($lead->message))
            ->action('Open demo request', route('superadmin.demo-requests.show', $lead));
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'type' => 'demo_request',
            'title' => "Demo request from {$this->demoRequest->society_name}, {$this->demoRequest->city}",
            'demo_request_id' => $this->demoRequest->id,
            'url' => route('superadmin.demo-requests.show', $this->demoRequest),
        ];
    }
}
