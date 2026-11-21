<?php

namespace App\Notifications;

use App\Models\AmenityBooking;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;

/**
 * Office: a resident asked for a slot. Resident: the office decided.
 */
class AmenityBookingUpdated extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(public readonly AmenityBooking $booking, public readonly bool $forOffice) {}

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
        $b = $this->booking->loadMissing(['amenity', 'member']);
        $when = $b->booking_date->format('d M').', '.$b->timeLabel();

        return [
            'type' => 'amenity_booking',
            'title' => $this->forOffice
                ? ($b->member?->name ?? 'A resident')." requested {$b->amenity?->name} on {$when}"
                : "Your {$b->amenity?->name} booking on {$when}: ".strtolower($b->statusLabel()),
            'booking_id' => $b->id,
            'url' => $this->forOffice ? route('society.amenities.bookings', ['status' => 'pending']) : route('member.amenities.index'),
        ];
    }
}
