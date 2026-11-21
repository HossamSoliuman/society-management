<?php

namespace App\Notifications;

use App\Models\SupportTicket;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class ComplaintAssigned extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(public readonly SupportTicket $ticket, public readonly User $assignedBy) {}

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
            ->subject("[{$ticket->ticket_number}] Complaint assigned to you: {$ticket->subject}")
            ->greeting("Hello {$notifiable->name},")
            ->line("{$this->assignedBy->name} assigned you a ".strtolower($ticket->priorityLabel())."-priority complaint in {$ticket->category}.")
            ->line($ticket->raised_by_name.($ticket->flat_no ? " ({$ticket->flat_no})" : '').': '.$ticket->description)
            ->action('Open complaint', route('society.complaints.show', $ticket));
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'type' => 'complaint_assigned',
            'title' => "Complaint {$this->ticket->ticket_number} assigned to you",
            'ticket_id' => $this->ticket->id,
            'url' => route('society.complaints.show', $this->ticket),
        ];
    }
}
