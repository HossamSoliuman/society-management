@extends('society.layouts.app')

@section('title', 'Amenities')

@section('content')
<div class="page-header">
    <div class="page-header-row">
        <div>
            <h1 class="page-title">Amenities</h1>
            <div class="breadcrumb" style="margin-top: 6px;">
                <a href="{{ route('society.dashboard') }}">Home</a>
                <span class="breadcrumb-separator">/</span>
                <span>Amenities</span>
            </div>
        </div>
        <div style="display: inline-flex; gap: 10px;">
            <a href="{{ route('society.amenities.bookings') }}" class="btn btn-secondary"><i class="fas fa-calendar-days"></i> Bookings</a>
            <a href="{{ route('society.amenities.create') }}" class="btn btn-primary"><i class="fas fa-plus"></i> Add Amenity</a>
        </div>
    </div>
</div>

<div class="stats-grid stats-grid-4">
    @include('society.partials.stat-card', ['icon' => 'fa-dumbbell', 'iconVariant' => 'peach', 'label' => 'Bookable Amenities', 'value' => number_format($stats['amenities'])])
    @include('society.partials.stat-card', ['icon' => 'fa-calendar-day', 'iconVariant' => 'info', 'label' => 'Bookings Today', 'value' => number_format($stats['today'])])
    @include('society.partials.stat-card', ['icon' => 'fa-hourglass-half', 'iconVariant' => 'warning', 'label' => 'Awaiting Approval', 'value' => number_format($stats['pending'])])
    @include('society.partials.stat-card', ['icon' => 'fa-indian-rupee-sign', 'iconVariant' => 'success', 'label' => 'Charges This Month', 'value' => e(format_inr($stats['income'], 0))])
</div>

<div class="grid-3">
    @forelse($amenities as $amenity)
        <div class="card">
            <div class="card-body">
                <div style="display: flex; align-items: flex-start; justify-content: space-between; gap: 10px;">
                    <div>
                        <div style="font-size: 16px; font-weight: 600;">{{ $amenity->name }}</div>
                        <div style="font-size: 12px; color: var(--text-muted); margin-top: 2px;">{{ $amenity->location ?? '—' }}{{ $amenity->capacity ? ' · up to '.$amenity->capacity.' people' : '' }}</div>
                    </div>
                    <span class="badge {{ $amenity->isActive() ? 'badge-success' : 'badge-secondary' }}">{{ $amenity->isActive() ? 'Open' : 'Closed' }}</span>
                </div>

                <div class="review-row" style="margin-top: 10px;"><span class="review-label">Hours</span><span class="review-value">{{ $amenity->hoursLabel() }} · max {{ $amenity->max_hours }}h</span></div>
                <div class="review-row"><span class="review-label">Charge</span><span class="review-value">{{ (float) $amenity->charge_per_hour > 0 ? format_inr($amenity->charge_per_hour, 0).' / hour' : 'Free' }}</span></div>
                <div class="review-row"><span class="review-label">Approval</span><span class="review-value">{{ $amenity->requires_approval ? 'Office approves resident requests' : 'Instant' }}</span></div>

                <div style="margin-top: 12px; font-size: 12px; font-weight: 600; color: var(--text-secondary);">Today</div>
                @forelse($amenity->bookings as $booking)
                    <div style="font-size: 12px; margin-top: 4px;"><span class="badge {{ $booking->statusBadgeClass() }}">{{ $booking->timeLabel() }}</span></div>
                @empty
                    <div style="font-size: 12px; color: var(--text-muted); margin-top: 4px;">No bookings</div>
                @endforelse

                <div style="display: flex; gap: 8px; margin-top: 14px; flex-wrap: wrap;">
                    <a href="{{ route('society.amenities.bookings.create', ['amenity' => $amenity->id]) }}" class="btn btn-primary btn-sm"><i class="fas fa-plus"></i> Book</a>
                    <a href="{{ route('society.amenities.bookings', ['amenity' => $amenity->id]) }}" class="btn btn-secondary btn-sm">{{ $amenity->upcoming_count }} upcoming{{ $amenity->pending_count ? ' · '.$amenity->pending_count.' pending' : '' }}</a>
                    <a href="{{ route('society.amenities.edit', $amenity) }}" class="btn btn-secondary btn-sm"><i class="fas fa-pencil"></i></a>
                </div>
            </div>
        </div>
    @empty
        <div class="card" style="grid-column: 1 / -1;">
            <div class="card-body">
                <div class="empty-state">
                    <div class="empty-state-icon"><i class="fas fa-dumbbell"></i></div>
                    <div class="empty-state-title">No amenities yet</div>
                    <div class="empty-state-text">Add the clubhouse, hall, courts or guest rooms residents can book.</div>
                </div>
            </div>
        </div>
    @endforelse
</div>
@endsection
