<?php

namespace App\Notifications;

use App\Models\SupportTicket;
use App\Models\TicketReply;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class TicketReplied extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(public readonly SupportTicket $ticket, public readonly TicketReply $reply) {}

    /**
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['mail', 'database'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject("[{$this->ticket->ticket_number}] New reply on: {$this->ticket->subject}")
            ->greeting("Hello {$notifiable->name},")
            ->line("{$this->reply->user?->name} replied:")
            ->line($this->reply->message)
            ->line('Current status: '.$this->ticket->statusLabel())
            ->action('View ticket', $this->urlFor($notifiable));
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'type' => 'ticket_replied',
            'title' => "Reply on {$this->ticket->ticket_number}",
            'ticket_id' => $this->ticket->id,
            'url' => $this->urlFor($notifiable),
        ];
    }

    private function urlFor(object $notifiable): string
    {
        if (! method_exists($notifiable, 'hasRole')) {
            return route('society.support.show', $this->ticket);
        }

        if ($notifiable->hasRole('super_admin')) {
            return route('superadmin.tickets.show', $this->ticket);
        }

        return $notifiable->hasRole('member')
            ? route('member.support.show', $this->ticket)
            : route('society.support.show', $this->ticket);
    }
}
