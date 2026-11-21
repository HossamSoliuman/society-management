@extends('society.layouts.app')

@section('title', 'Amenity Bookings')

@section('content')
<div class="page-header">
    <div class="page-header-row">
        <div>
            <h1 class="page-title">Amenity Bookings</h1>
            <div class="breadcrumb" style="margin-top: 6px;">
                <a href="{{ route('society.dashboard') }}">Home</a>
                <span class="breadcrumb-separator">/</span>
                <a href="{{ route('society.amenities.index') }}">Amenities</a>
                <span class="breadcrumb-separator">/</span>
                <span>Bookings</span>
            </div>
        </div>
        <a href="{{ route('society.amenities.bookings.create') }}" class="btn btn-primary"><i class="fas fa-plus"></i> New Booking</a>
    </div>
</div>

<div class="card">
    <div class="card-body">
        <form method="GET" action="{{ route('society.amenities.bookings') }}" style="display: grid; grid-template-columns: 200px 190px 160px auto auto; gap: 12px; align-items: end; margin-bottom: 16px;">
            <select name="amenity" class="form-control">
                <option value="">All Amenities</option>
                @foreach($amenities as $amenity)
                    <option value="{{ $amenity->id }}" @selected((string) request('amenity') === (string) $amenity->id)>{{ $amenity->name }}</option>
                @endforeach
            </select>
            <select name="status" class="form-control">
                <option value="">Any status</option>
                @foreach($statuses as $value => $meta)
                    <option value="{{ $value }}" @selected($status === $value)>{{ $meta[0] }}</option>
                @endforeach
            </select>
            <select name="when" class="form-control">
                <option value="upcoming" @selected($when === 'upcoming')>Upcoming</option>
                <option value="past" @selected($when === 'past')>Past</option>
            </select>
            <button type="submit" class="btn btn-secondary"><i class="fas fa-filter"></i> Filter</button>
            <a href="{{ route('society.amenities.bookings') }}" class="btn btn-secondary"><i class="fas fa-rotate"></i> Reset</a>
        </form>

        <div class="table-responsive">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Amenity</th>
                        <th>Date &amp; Time</th>
                        <th>Resident</th>
                        <th>Guests</th>
                        <th>Charge</th>
                        <th>Status</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($bookings as $booking)
                        <tr>
                            <td>
                                <span style="font-weight: 600;">{{ $booking->amenity?->name }}</span>
                                @if($booking->purpose)<div style="font-size: 11px; color: var(--text-muted);">{{ $booking->purpose }}</div>@endif
                            </td>
                            <td style="white-space: nowrap;">{{ $booking->booking_date->format('D, d M Y') }}<div style="font-size: 11px; color: var(--text-muted);">{{ $booking->timeLabel() }}</div></td>
                            <td>{{ $booking->member?->name ?? '—' }}<div style="font-size: 11px; color: var(--text-muted);">{{ $booking->unit?->unit_number }}</div></td>
                            <td>{{ $booking->guests }}</td>
                            <td style="white-space: nowrap;">
                                {{ (float) $booking->charge > 0 ? format_inr($booking->charge, 0) : 'Free' }}
                                @if($booking->bill)
                                    <div style="font-size: 11px;"><a href="{{ route('society.billing.bills.show', $booking->bill) }}">{{ $booking->bill->bill_number }}</a></div>
                                @endif
                            </td>
                            <td>
                                <span class="badge {{ $booking->statusBadgeClass() }}">{{ $booking->statusLabel() }}</span>
                                @if($booking->decision_note)<div style="font-size: 11px; color: var(--text-muted);">{{ $booking->decision_note }}</div>@endif
                            </td>
                            <td>
                                <div style="display: inline-flex; gap: 6px;">
                                    @if($booking->status === 'pending')
                                        <form method="POST" action="{{ route('society.amenities.bookings.approve', $booking) }}">
                                            @csrf
                                            <button type="submit" class="btn btn-primary btn-sm"><i class="fas fa-check"></i> Approve</button>
                                        </form>
                                        <form method="POST" action="{{ route('society.amenities.bookings.reject', $booking) }}" data-confirm="Reject this booking request?" data-confirm-label="Reject">
                                            @csrf
                                            <button type="submit" class="btn btn-secondary btn-sm">Reject</button>
                                        </form>
                                    @elseif($booking->status === 'approved' && $booking->booking_date->gte(today()))
                                        <form method="POST" action="{{ route('society.amenities.bookings.cancel', $booking) }}" data-confirm="Cancel this booking? An unpaid charge bill for it is cancelled too." data-confirm-label="Cancel Booking">
                                            @csrf
                                            <button type="submit" class="btn btn-secondary btn-sm">Cancel</button>
                                        </form>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7">
                                <div class="empty-state">
                                    <div class="empty-state-icon"><i class="fas fa-calendar-days"></i></div>
                                    <div class="empty-state-title">No bookings</div>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @include('society.partials.pagination', ['items' => $bookings, 'unit' => 'bookings'])
    </div>
</div>
@endsection
