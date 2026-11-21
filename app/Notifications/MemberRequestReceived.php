<?php

namespace App\Notifications;

use App\Models\MemberRequest;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;

class MemberRequestReceived extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(public readonly MemberRequest $request) {}

    /**
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['database'];
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        $r = $this->request;

        return [
            'type' => 'member_request',
            'title' => "{$r->name} asked to join as ".strtolower($r->memberTypeLabel())." of {$r->unit_number}",
            'member_request_id' => $r->id,
            'url' => route('society.member-requests.show', $r),
        ];
    }
}
