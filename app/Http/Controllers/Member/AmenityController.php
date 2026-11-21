<?php

namespace App\Http\Controllers\Member;

use App\Models\Amenity;
use App\Models\AmenityBooking;
use App\Services\AmenityBookingService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

/**
 * Residents book society amenities and track their requests.
 */
class AmenityController extends PortalController
{
    public function __construct(private readonly AmenityBookingService $bookings) {}

    public function index(): View
    {
        $member = $this->currentMember()->load('society');

        return view('member.amenities.index', [
            'member' => $member,
            'enabled' => (bool) $member->society?->setting('portal_amenity_booking_enabled'),
            'window' => (int) $member->society?->setting('amenity_booking_window_days'),
            'amenities' => Amenity::query()->where('society_id', $member->society_id)->where('status', 'active')->orderBy('name')->get(),
            'bookings' => AmenityBooking::query()
                ->where('society_id', $member->society_id)
                ->where('member_id', $member->id)
                ->with('amenity')
                ->latest('booking_date')
                ->limit(20)
                ->get(),
            'taken' => AmenityBooking::query()
                ->where('society_id', $member->society_id)
                ->holding()
                ->whereBetween('booking_date', [Carbon::today(), Carbon::today()->addDays(14)])
                ->with('amenity')
                ->orderBy('booking_date')
                ->orderBy('start_time')
                ->get()
                ->groupBy('amenity_id'),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $member = $this->currentMember()->load('society');

        if (! $member->society?->setting('portal_amenity_booking_enabled')) {
            return redirect()->route('member.amenities.index')->with('error', 'Please contact the society office to book an amenity.');
        }

        $data = $request->validate([
            'amenity_id' => ['required', Rule::exists('amenities', 'id')->where('society_id', $member->society_id)->where('status', 'active')],
            'booking_date' => ['required', 'date'],
            'start_time' => ['required', 'date_format:H:i'],
            'end_time' => ['required', 'date_format:H:i'],
            'guests' => ['nullable', 'integer', 'min:0', 'max:1000'],
            'purpose' => ['nullable', 'string', 'max:255'],
        ], [], ['amenity_id' => 'amenity']);

        $booking = $this->bookings->book($member->society, Amenity::findOrFail($data['amenity_id']), $data, $member, $member->primaryUnit(), $request->user(), byOffice: false);

        return redirect()->route('member.amenities.index')->with('success', $booking->status === 'approved'
            ? "{$booking->amenity->name} booked for {$booking->booking_date->format('d M Y')}, {$booking->timeLabel()}."
            : 'Booking requested. The society office will confirm it.');
    }

    public function cancel(Request $request, AmenityBooking $booking): RedirectResponse
    {
        $this->ownedByMember($booking->member_id);
        abort_unless(in_array($booking->status, ['pending', 'approved'], true) && $booking->booking_date->gte(Carbon::today()), 404);

        $this->bookings->cancel($booking, $request->user());

        return redirect()->route('member.amenities.index')->with('success', 'Booking cancelled.');
    }
}
