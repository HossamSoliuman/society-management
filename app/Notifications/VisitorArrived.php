<?php

namespace App\Notifications;

use App\Models\Visitor;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;

class VisitorArrived extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(public readonly Visitor $visitor) {}

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
        $v = $this->visitor;

        return [
            'type' => 'visitor_arrival',
            'title' => "{$v->name} ({$v->purposeLabel()}) checked in at the gate".($v->unit ? " for {$v->unit->unit_number}" : ''),
            'visitor_id' => $v->id,
            'url' => route('member.visitors.index'),
        ];
    }
}
