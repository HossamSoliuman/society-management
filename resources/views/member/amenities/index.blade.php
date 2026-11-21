@extends('member.layouts.app')

@section('title', 'Amenities')

@section('content')
<div class="page-header">
    <div class="page-header-row">
        <div>
            <h1 class="page-title">Amenities</h1>
            <div class="breadcrumb" style="margin-top: 6px;">
                <a href="{{ route('member.dashboard') }}">Home</a>
                <span class="breadcrumb-separator">/</span>
                <span>Amenities</span>
            </div>
        </div>
    </div>
</div>

@if($errors->any())
    <div class="alert alert-danger" style="margin-bottom: 20px;">
        <i class="fas fa-exclamation-circle"></i>
        <div>
            @foreach($errors->all() as $error)
                <div>{{ $error }}</div>
            @endforeach
        </div>
    </div>
@endif

<div class="content-grid">
    <div>
        <div class="card">
            <div class="card-body">
                <div class="section-title" style="font-size: 16px;">My Bookings</div>
                <div class="table-responsive">
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th>Amenity</th>
                                <th>Date &amp; Time</th>
                                <th>Charge</th>
                                <th>Status</th>
                                <th></th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($bookings as $booking)
                                <tr>
                                    <td style="font-weight: 600;">{{ $booking->amenity?->name }}</td>
                                    <td style="white-space: nowrap;">{{ $booking->booking_date->format('D, d M Y') }}<div style="font-size: 11px; color: var(--text-muted);">{{ $booking->timeLabel() }}</div></td>
                                    <td>{{ (float) $booking->charge > 0 ? format_inr($booking->charge, 0) : 'Free' }}</td>
                                    <td>
                                        <span class="badge {{ $booking->statusBadgeClass() }}">{{ $booking->statusLabel() }}</span>
                                        @if($booking->decision_note)<div style="font-size: 11px; color: var(--text-muted);">{{ $booking->decision_note }}</div>@endif
                                    </td>
                                    <td>
                                        @if(in_array($booking->status, ['pending', 'approved'], true) && $booking->booking_date->gte(today()))
                                            <form method="POST" action="{{ route('member.amenities.cancel', $booking) }}" data-confirm="Cancel your {{ $booking->amenity?->name }} booking?" data-confirm-label="Cancel Booking">
                                                @csrf
                                                <button type="submit" class="action-btn delete" title="Cancel"><i class="fas fa-ban"></i></button>
                                            </form>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr><td colspan="5" style="color: var(--text-muted);">No bookings yet.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <div class="card">
            <div class="card-body">
                <div class="section-title" style="font-size: 16px;">Already Booked (next 14 days)</div>
                @forelse($amenities as $amenity)
                    @php $slots = $taken[$amenity->id] ?? collect(); @endphp
                    <div style="padding: 10px 0; border-bottom: 1px solid var(--border-color);">
                        <div style="font-weight: 600;">{{ $amenity->name }} <span style="font-weight: 400; font-size: 12px; color: var(--text-muted);">· {{ $amenity->hoursLabel() }} · {{ (float) $amenity->charge_per_hour > 0 ? format_inr($amenity->charge_per_hour, 0).'/hour' : 'free' }}</span></div>
                        <div style="display: flex; flex-wrap: wrap; gap: 6px; margin-top: 6px;">
                            @forelse($slots as $slot)
                                <span class="badge badge-secondary">{{ $slot->booking_date->format('d M') }} {{ $slot->timeLabel() }}</span>
                            @empty
                                <span style="font-size: 12px; color: var(--text-muted);">All free</span>
                            @endforelse
                        </div>
                    </div>
                @empty
                    <div style="font-size: 13px; color: var(--text-muted);">The society has not listed any amenities yet.</div>
                @endforelse
            </div>
        </div>
    </div>

    <div>
        @if($enabled && $amenities->isNotEmpty())
            <div class="card">
                <div class="card-body">
                    <div class="section-title" style="font-size: 15px;">Book an Amenity</div>
                    <form method="POST" action="{{ route('member.amenities.store') }}">
                        @csrf
                        <div class="form-group">
                            <label class="form-label">Amenity <span class="required">*</span></label>
                            <select name="amenity_id" class="form-control" required>
                                @foreach($amenities as $amenity)
                                    <option value="{{ $amenity->id }}" @selected((string) old('amenity_id') === (string) $amenity->id)>{{ $amenity->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="form-group">
                            <label class="form-label">Date <span class="required">*</span></label>
                            <input type="date" name="booking_date" class="form-control" min="{{ now()->format('Y-m-d') }}" max="{{ now()->addDays($window)->format('Y-m-d') }}" value="{{ old('booking_date', now()->addDay()->format('Y-m-d')) }}" required>
                        </div>
                        <div class="form-row">
                            <div class="form-group">
                                <label class="form-label">From</label>
                                <input type="time" name="start_time" class="form-control" value="{{ old('start_time', '10:00') }}" required>
                            </div>
                            <div class="form-group">
                                <label class="form-label">To</label>
                                <input type="time" name="end_time" class="form-control" value="{{ old('end_time', '12:00') }}" required>
                            </div>
                        </div>
                        <div class="form-row">
                            <div class="form-group">
                                <label class="form-label">Guests</label>
                                <input type="number" name="guests" class="form-control" min="0" value="{{ old('guests', 0) }}">
                            </div>
                            <div class="form-group">
                                <label class="form-label">Purpose</label>
                                <input type="text" name="purpose" class="form-control" value="{{ old('purpose') }}">
                            </div>
                        </div>
                        <button type="submit" class="btn btn-primary" style="width: 100%;"><i class="fas fa-calendar-check"></i> Request Booking</button>
                    </form>
                </div>
            </div>
        @elseif(! $enabled)
            <div class="info-box">
                <i class="fas fa-circle-info"></i>
                <span>Please contact the society office to book an amenity.</span>
            </div>
        @endif
    </div>
</div>
@endsection
