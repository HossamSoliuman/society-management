<?php

namespace App\Services;

use App\Models\Amenity;
use App\Models\AmenityBooking;
use App\Models\MaintenanceBill;
use App\Models\Member;
use App\Models\Society;
use App\Models\Unit;
use App\Models\User;
use App\Notifications\AmenityBookingUpdated;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

/**
 * Amenity bookings: slot validation (opening hours, length, booking window,
 * no overlap), approval with an optional charge billed to the unit, and
 * cancellation.
 */
class AmenityBookingService
{
    public function __construct(private readonly BillingService $billing) {}

    /**
     * @param  array{booking_date: string, start_time: string, end_time: string, guests?: int|null, purpose?: string|null}  $data
     */
    public function book(Society $society, Amenity $amenity, array $data, ?Member $member, ?Unit $unit, User $bookedBy, bool $byOffice): AmenityBooking
    {
        $date = Carbon::parse($data['booking_date'])->startOfDay();
        $start = substr($data['start_time'], 0, 5);
        $end = substr($data['end_time'], 0, 5);

        $this->guard($society, $amenity, $date, $start, $end, $byOffice);

        $booking = DB::transaction(function () use ($society, $amenity, $data, $member, $unit, $bookedBy, $date, $start, $end): AmenityBooking {
            Amenity::query()->whereKey($amenity->id)->lockForUpdate()->first();

            $clash = AmenityBooking::query()
                ->where('amenity_id', $amenity->id)
                ->whereDate('booking_date', $date->toDateString())
                ->holding()
                ->where('start_time', '<', $end)
                ->where('end_time', '>', $start)
                ->first();

            if ($clash) {
                throw ValidationException::withMessages([
                    'start_time' => "{$amenity->name} is already booked {$clash->timeLabel()} on {$date->format('d M Y')}.",
                ]);
            }

            $hours = (strtotime($end) - strtotime($start)) / 3600;

            return AmenityBooking::create([
                'society_id' => $society->id,
                'amenity_id' => $amenity->id,
                'member_id' => $member?->id,
                'unit_id' => $unit?->id,
                'booked_by' => $bookedBy->id,
                'booking_date' => $date->toDateString(),
                'start_time' => $start,
                'end_time' => $end,
                'guests' => (int) ($data['guests'] ?? 0),
                'purpose' => $data['purpose'] ?? null,
                'charge' => round($hours * (float) $amenity->charge_per_hour, 2),
                'status' => 'pending',
            ]);
        });

        if ($byOffice || ! $amenity->requires_approval) {
            $this->approve($booking, $bookedBy, notify: ! $byOffice);
        } else {
            $this->notifyOffice($booking);
        }

        return $booking->fresh();
    }

    public function approve(AmenityBooking $booking, User $by, bool $notify = true): AmenityBooking
    {
        DB::transaction(function () use ($booking, $by) {
            $booking->forceFill(['status' => 'approved', 'decided_by' => $by->id, 'decided_at' => now()])->save();

            if ((float) $booking->charge > 0 && ! $booking->maintenance_bill_id) {
                $booking->forceFill(['maintenance_bill_id' => $this->bill($booking)->id])->save();
            }
        });

        if ($notify) {
            $this->notifyResident($booking);
        }

        return $booking;
    }

    public function reject(AmenityBooking $booking, User $by, ?string $note): AmenityBooking
    {
        $booking->forceFill(['status' => 'rejected', 'decided_by' => $by->id, 'decided_at' => now(), 'decision_note' => $note])->save();
        $this->notifyResident($booking);

        return $booking;
    }

    /**
     * Cancel a booking; an unpaid charge bill for it is cancelled too.
     */
    public function cancel(AmenityBooking $booking, User $by): AmenityBooking
    {
        DB::transaction(function () use ($booking, $by) {
            $booking->forceFill(['status' => 'cancelled', 'decided_by' => $by->id, 'decided_at' => now()])->save();

            $bill = $booking->bill;
            if ($bill && (float) $bill->collected_amount <= 0 && $bill->status !== 'cancelled') {
                $bill->forceFill(['status' => 'cancelled', 'outstanding_amount' => 0])->save();
            }
        });

        if (! $by->hasRole('member')) {
            $this->notifyResident($booking);
        }

        return $booking;
    }

    private function guard(Society $society, Amenity $amenity, Carbon $date, string $start, string $end, bool $byOffice): void
    {
        $errors = [];
        $open = substr((string) $amenity->open_time, 0, 5);
        $close = substr((string) $amenity->close_time, 0, 5);
        $hours = (strtotime($end) - strtotime($start)) / 3600;
        $window = (int) $society->setting('amenity_booking_window_days');

        if (! $amenity->isActive()) {
            $errors['amenity_id'] = "{$amenity->name} is not open for booking.";
        }
        if ($date->lt(Carbon::today()) || ($date->isToday() && $start <= now()->format('H:i'))) {
            $errors['booking_date'] = 'Pick a time in the future.';
        }
        if (! $byOffice && $date->gt(Carbon::today()->addDays($window))) {
            $errors['booking_date'] = "Bookings open {$window} days ahead.";
        }
        if ($end <= $start) {
            $errors['end_time'] = 'End time must be after the start time.';
        } elseif ($start < $open || $end > $close) {
            $errors['start_time'] = "{$amenity->name} is open {$open}–{$close}.";
        } elseif ($hours > $amenity->max_hours) {
            $errors['end_time'] = "Bookings are limited to {$amenity->max_hours} hour(s).";
        }

        if ($errors !== []) {
            throw ValidationException::withMessages($errors);
        }
    }

    private function bill(AmenityBooking $booking): MaintenanceBill
    {
        $booking->loadMissing(['amenity', 'member', 'unit', 'society']);
        $today = Carbon::today();

        return $this->billing->createBill($booking->society, [
            'member' => $booking->member,
            'unit' => $booking->unit,
            'bill_month' => $booking->booking_date->format('F Y'),
            'bill_date' => $today,
            'due_date' => $today->copy()->addDays(15),
            'billing_type' => 'Amenity Booking',
            'lines' => [[
                'charge_head_name' => 'Amenity Booking',
                'description' => "{$booking->amenity->name} · {$booking->booking_date->format('d M Y')} · {$booking->timeLabel()}",
                'amount' => (float) $booking->charge,
            ]],
            'notes' => "Charge for amenity booking #{$booking->id}",
        ]);
    }

    private function notifyOffice(AmenityBooking $booking): void
    {
        User::query()
            ->where('society_id', $booking->society_id)
            ->where('status', 'active')
            ->whereHas('roles', fn ($q) => $q->whereIn('name', ['society_admin', 'manager']))
            ->get()
            ->each(fn (User $user) => $user->notify(new AmenityBookingUpdated($booking, forOffice: true)));
    }

    private function notifyResident(AmenityBooking $booking): void
    {
        $user = $booking->member?->user;

        if ($user && $user->status === 'active') {
            $user->notify(new AmenityBookingUpdated($booking, forOffice: false));
        }
    }
}
