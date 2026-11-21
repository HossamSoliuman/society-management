<?php

namespace App\Services;

use App\Models\PrefixSetting;
use App\Models\Society;
use App\Models\SupportTicket;
use App\Models\TicketReply;
use App\Models\User;
use App\Notifications\ComplaintAssigned;
use App\Notifications\TicketCreated;
use App\Notifications\TicketReplied;
use App\Notifications\TicketStatusChanged;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;

/**
 * Ticket workflow shared by the society panel, the super-admin desk and the
 * member portal: creation, threaded replies and status transitions, each
 * with the matching notifications.
 */
class TicketService
{
    /**
     * @param  array<string, mixed>  $attributes
     * @param  bool  $complaintDesk  a resident complaint logged by the society office:
     *                               notify the office team instead of the platform desk
     */
    public function create(Society $society, array $attributes, ?User $createdBy = null, bool $complaintDesk = false): SupportTicket
    {
        $ticket = DB::transaction(function () use ($society, $attributes, $createdBy): SupportTicket {
            return SupportTicket::create(array_merge($attributes, [
                'society_id' => $society->id,
                'ticket_number' => PrefixSetting::generate('Ticket'),
                'created_by' => $createdBy?->id,
                'status' => $attributes['status'] ?? 'open',
                'raised_at' => $attributes['raised_at'] ?? now(),
            ]));
        });

        // Resident complaints go to the society office; society tickets go to
        // the platform support desk.
        $recipients = match (true) {
            $complaintDesk => $this->societyAdmins($ticket)->reject(fn (User $user) => $user->is($createdBy))->values(),
            (bool) $createdBy?->hasRole('member') => $this->societyAdmins($ticket),
            default => $this->superAdmins(),
        };
        foreach ($recipients as $recipient) {
            $recipient->notify(new TicketCreated($ticket));
        }

        return $ticket;
    }

    /**
     * Post a reply; optionally move the status at the same time. Society-side
     * replies notify super admins, super-admin replies notify society admins.
     */
    public function reply(SupportTicket $ticket, User $author, string $message, ?string $status = null, ?string $attachment = null, bool $complaintDesk = false): TicketReply
    {
        $reply = DB::transaction(function () use ($ticket, $author, $message, $status, $attachment): TicketReply {
            $reply = $ticket->replies()->create([
                'user_id' => $author->id,
                'message' => $message,
                'attachment' => $attachment,
            ]);

            $update = ['last_reply_at' => now()];
            if ($status && $status !== $ticket->status) {
                $update += $this->statusAttributes($status);
            }
            $ticket->forceFill($update)->save();

            return $reply;
        });

        foreach ($this->counterparts($ticket, $author, $complaintDesk) as $recipient) {
            $recipient->notify(new TicketReplied($ticket, $reply));
        }

        return $reply;
    }

    public function changeStatus(SupportTicket $ticket, string $status, User $changedBy, bool $complaintDesk = false): SupportTicket
    {
        if ($status === $ticket->status) {
            return $ticket;
        }

        $previous = $ticket->status;
        $ticket->forceFill($this->statusAttributes($status))->save();

        foreach ($this->counterparts($ticket, $changedBy, $complaintDesk) as $recipient) {
            $recipient->notify(new TicketStatusChanged($ticket, $previous));
        }

        return $ticket;
    }

    /**
     * Hand a complaint to a society team member; an untouched complaint
     * moves to "in progress" at the same time.
     */
    public function assign(SupportTicket $ticket, ?User $assignee, User $assignedBy): SupportTicket
    {
        $update = ['assigned_to' => $assignee?->id];
        if ($assignee && $ticket->status === 'open') {
            $update += $this->statusAttributes('in_progress');
        }
        $ticket->forceFill($update)->save();

        if ($assignee && ! $assignee->is($assignedBy)) {
            $assignee->notify(new ComplaintAssigned($ticket, $assignedBy));
        }

        return $ticket;
    }

    /**
     * @return array<string, mixed>
     */
    private function statusAttributes(string $status): array
    {
        return [
            'status' => $status,
            'resolved_at' => in_array($status, ['resolved', 'closed'], true) ? now() : null,
        ];
    }

    /**
     * Whoever is on the other side of the conversation from $actor.
     *
     * @return Collection<int, User>
     */
    private function counterparts(SupportTicket $ticket, User $actor, bool $complaintDesk = false): Collection
    {
        if ($actor->hasRole('super_admin')) {
            return $this->societyAdmins($ticket);
        }

        if ($actor->hasRole('member')) {
            return $this->societyAdmins($ticket);
        }

        // Society staff acting on a resident's ticket talk to the resident,
        // otherwise (a platform ticket) to the support desk.
        $memberUser = $this->memberUser($ticket);

        if ($complaintDesk) {
            return $memberUser ? new Collection([$memberUser]) : new Collection;
        }

        return $memberUser ? new Collection([$memberUser]) : $this->superAdmins();
    }

    /**
     * The portal login of the resident who raised the ticket, if any.
     */
    private function memberUser(SupportTicket $ticket): ?User
    {
        if ($ticket->raised_by_type !== 'member' || ! $ticket->member_id) {
            return null;
        }

        $user = $ticket->member?->user;

        return $user && $user->status === 'active' ? $user : null;
    }

    /**
     * @return Collection<int, User>
     */
    private function superAdmins(): Collection
    {
        return User::query()
            ->where('status', 'active')
            ->whereHas('roles', fn ($q) => $q->where('name', 'super_admin'))
            ->get();
    }

    /**
     * @return Collection<int, User>
     */
    private function societyAdmins(SupportTicket $ticket): Collection
    {
        if (! $ticket->society_id) {
            return new Collection;
        }

        return User::query()
            ->where('society_id', $ticket->society_id)
            ->where('status', 'active')
            ->whereHas('roles', fn ($q) => $q->whereIn('name', ['society_admin', 'manager']))
            ->get();
    }
}
