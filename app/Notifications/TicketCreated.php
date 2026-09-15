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
            ->line("{$this->raisedBy()} raised a ".strtolower($ticket->priorityLabel())."-priority ticket in {$ticket->category}.")
            ->line($ticket->description)
            ->action('Open ticket', $this->urlFor($notifiable));
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
            'url' => $this->urlFor($notifiable),
        ];
    }

    private function raisedBy(): string
    {
        return $this->ticket->raised_by_type === 'member'
            ? ($this->ticket->raised_by_name ?: 'A resident').($this->ticket->flat_no ? " ({$this->ticket->flat_no})" : '')
            : ($this->ticket->society?->name ?? 'A society');
    }

    private function urlFor(object $notifiable): string
    {
        return method_exists($notifiable, 'hasRole') && $notifiable->hasRole('super_admin')
            ? route('superadmin.tickets.show', $this->ticket)
            : route('society.support.show', $this->ticket);
    }
}
