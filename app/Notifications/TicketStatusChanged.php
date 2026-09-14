<?php

namespace App\Notifications;

use App\Models\SupportTicket;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class TicketStatusChanged extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(public readonly SupportTicket $ticket, public readonly string $previousStatus) {}

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
            ->subject("[{$this->ticket->ticket_number}] Status changed to {$this->ticket->statusLabel()}")
            ->greeting("Hello {$notifiable->name},")
            ->line("Ticket \"{$this->ticket->subject}\" moved from ".ucwords(str_replace('_', ' ', $this->previousStatus))." to {$this->ticket->statusLabel()}.")
            ->action('View ticket', $this->urlFor($notifiable));
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'type' => 'ticket_status',
            'title' => "{$this->ticket->ticket_number} is now {$this->ticket->statusLabel()}",
            'ticket_id' => $this->ticket->id,
            'url' => $this->urlFor($notifiable),
        ];
    }

    private function urlFor(object $notifiable): string
    {
        return method_exists($notifiable, 'hasRole') && $notifiable->hasRole('super_admin')
            ? route('superadmin.tickets.show', $this->ticket)
            : route('society.support.show', $this->ticket);
    }
}
