<?php

namespace App\Http\Controllers\Society;

use App\Http\Controllers\Controller;
use App\Models\Amenity;
use App\Models\AmenityBooking;
use App\Models\Member;
use App\Services\AmenityBookingService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class AmenityController extends Controller
{
    public function __construct(private readonly AmenityBookingService $bookings) {}

    public function index(): View
    {
        $society = $this->currentSociety();
        $today = Carbon::today();

        $amenities = Amenity::query()
            ->forSociety($society)
            ->withCount([
                'bookings as upcoming_count' => fn ($q) => $q->holding()->whereDate('booking_date', '>=', $today),
                'bookings as pending_count' => fn ($q) => $q->where('status', 'pending'),
            ])
            ->with(['bookings' => fn ($q) => $q->holding()->whereDate('booking_date', $today)->orderBy('start_time')])
            ->orderBy('name')
            ->get();

        $base = AmenityBooking::query()->forSociety($society);

        return view('society.amenities.index', [
            'amenities' => $amenities,
            'stats' => [
                'amenities' => $amenities->where('status', 'active')->count(),
                'today' => (clone $base)->holding()->whereDate('booking_date', $today)->count(),
                'pending' => (clone $base)->where('status', 'pending')->count(),
                'income' => (float) (clone $base)->where('status', 'approved')->whereBetween('booking_date', [$today->copy()->startOfMonth(), $today->copy()->endOfMonth()])->sum('charge'),
            ],
        ]);
    }

    public function create(): View
    {
        return view('society.amenities.form', ['amenity' => new Amenity(['open_time' => '06:00', 'close_time' => '22:00', 'max_hours' => 4, 'requires_approval' => true, 'status' => 'active'])]);
    }

    public function store(Request $request): RedirectResponse
    {
        $amenity = Amenity::create($this->validated($request) + ['society_id' => $this->currentSociety()->id]);

        return redirect()->route('society.amenities.index')->with('success', "{$amenity->name} added.");
    }

    public function edit(Amenity $amenity): View
    {
        return view('society.amenities.form', ['amenity' => $amenity]);
    }

    public function update(Request $request, Amenity $amenity): RedirectResponse
    {
        $amenity->update($this->validated($request));

        return redirect()->route('society.amenities.index')->with('success', "{$amenity->name} updated.");
    }

    public function bookings(Request $request): View
    {
        $society = $this->currentSociety();
        $status = array_key_exists((string) $request->query('status'), AmenityBooking::STATUSES) ? (string) $request->query('status') : null;
        $when = in_array($request->query('when'), ['upcoming', 'past'], true) ? (string) $request->query('when') : 'upcoming';

        $bookings = AmenityBooking::query()
            ->forSociety($society)
            ->with(['amenity', 'member', 'unit', 'bill'])
            ->when($status, fn ($q) => $q->where('status', $status))
            ->when($request->filled('amenity'), fn ($q) => $q->where('amenity_id', $request->integer('amenity')))
            ->when($when === 'upcoming', fn ($q) => $q->whereDate('booking_date', '>=', Carbon::today())->orderBy('booking_date')->orderBy('start_time'))
            ->when($when === 'past', fn ($q) => $q->whereDate('booking_date', '<', Carbon::today())->orderByDesc('booking_date')->orderByDesc('start_time'))
            ->paginate(15)
            ->withQueryString();

        return view('society.amenities.bookings', [
            'bookings' => $bookings,
            'status' => $status,
            'when' => $when,
            'statuses' => AmenityBooking::STATUSES,
            'amenities' => Amenity::query()->forSociety($society)->orderBy('name')->get(['id', 'name']),
        ]);
    }

    public function createBooking(Request $request): View
    {
        $society = $this->currentSociety();

        return view('society.amenities.book', [
            'amenities' => Amenity::query()->forSociety($society)->where('status', 'active')->orderBy('name')->get(),
            'members' => Member::query()->forSociety($society)->where('status', 'active')->whereIn('member_type', ['owner', 'tenant'])->orderBy('name')->get(['id', 'name', 'flat_unit']),
            'selected' => $request->integer('amenity') ?: null,
        ]);
    }

    public function storeBooking(Request $request): RedirectResponse
    {
        $society = $this->currentSociety();
        $data = $request->validate([
            'amenity_id' => ['required', Rule::exists('amenities', 'id')->where('society_id', $society->id)],
            'member_id' => ['required', Rule::exists('members', 'id')->where('society_id', $society->id)->whereNull('deleted_at')],
            'booking_date' => ['required', 'date'],
            'start_time' => ['required', 'date_format:H:i'],
            'end_time' => ['required', 'date_format:H:i'],
            'guests' => ['nullable', 'integer', 'min:0', 'max:1000'],
            'purpose' => ['nullable', 'string', 'max:255'],
        ], [], ['amenity_id' => 'amenity', 'member_id' => 'resident']);

        $member = Member::find($data['member_id']);
        $booking = $this->bookings->book($society, Amenity::findOrFail($data['amenity_id']), $data, $member, $member?->primaryUnit(), $request->user(), byOffice: true);

        return redirect()->route('society.amenities.bookings')
            ->with('success', "{$booking->amenity->name} booked for {$member->name} on {$booking->booking_date->format('d M Y')}, {$booking->timeLabel()}.".((float) $booking->charge > 0 ? ' A bill of '.format_inr($booking->charge, 0).' was raised.' : ''));
    }

    public function approve(Request $request, AmenityBooking $booking): RedirectResponse
    {
        abort_unless($booking->status === 'pending', 404);
        $this->bookings->approve($booking, $request->user());

        return back()->with('success', 'Booking confirmed.'.((float) $booking->charge > 0 ? ' A bill of '.format_inr($booking->charge, 0).' was raised.' : ''));
    }

    public function reject(Request $request, AmenityBooking $booking): RedirectResponse
    {
        abort_unless($booking->status === 'pending', 404);
        $data = $request->validate(['decision_note' => ['nullable', 'string', 'max:255']]);
        $this->bookings->reject($booking, $request->user(), $data['decision_note'] ?? null);

        return back()->with('success', 'Booking rejected.');
    }

    public function cancel(Request $request, AmenityBooking $booking): RedirectResponse
    {
        abort_unless(in_array($booking->status, ['pending', 'approved'], true), 404);
        $this->bookings->cancel($booking, $request->user());

        return back()->with('success', 'Booking cancelled.');
    }

    /**
     * @return array<string, mixed>
     */
    private function validated(Request $request): array
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:2000'],
            'location' => ['nullable', 'string', 'max:255'],
            'capacity' => ['nullable', 'integer', 'min:1', 'max:10000'],
            'open_time' => ['required', 'date_format:H:i'],
            'close_time' => ['required', 'date_format:H:i', 'after:open_time'],
            'max_hours' => ['required', 'integer', 'min:1', 'max:24'],
            'charge_per_hour' => ['nullable', 'numeric', 'min:0', 'max:100000'],
            'requires_approval' => ['nullable', 'boolean'],
            'status' => ['required', 'in:active,inactive'],
        ]);

        return $data + [
            'requires_approval' => $request->boolean('requires_approval'),
            'charge_per_hour' => $data['charge_per_hour'] ?? 0,
        ];
    }
}
