<?php

namespace App\Notifications;

use App\Models\SupportTicket;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class TicketCreated extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(public readonly SupportTicket $ticket) {}

    /**
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['mail', 'database'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $ticket = $this->ticket;

        return (new MailMessage)
            ->subject("[{$ticket->ticket_number}] New support ticket: {$ticket->subject}")
            ->greeting("Hello {$notifiable->name},")
            ->line("{$ticket->society?->name} raised a ".strtolower($ticket->priorityLabel())."-priority ticket in {$ticket->category}.")
            ->line($ticket->description)
            ->action('Open ticket', route('superadmin.tickets.show', $ticket));
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'type' => 'ticket_created',
            'title' => "New ticket {$this->ticket->ticket_number}",
            'ticket_id' => $this->ticket->id,
            'url' => route('superadmin.tickets.show', $this->ticket),
        ];
    }
}
