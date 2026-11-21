<?php

namespace App\Models;

use App\Models\Concerns\BelongsToSociety;
use App\Models\Concerns\LogsActivity;
use Database\Factories\AmenityBookingFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AmenityBooking extends Model
{
    use BelongsToSociety, LogsActivity;

    /** @use HasFactory<AmenityBookingFactory> */
    use HasFactory;

    public const STATUSES = [
        'pending' => ['Awaiting Approval', 'badge-warning'],
        'approved' => ['Confirmed', 'badge-success'],
        'rejected' => ['Rejected', 'badge-danger'],
        'cancelled' => ['Cancelled', 'badge-secondary'],
    ];

    protected $fillable = [
        'society_id', 'amenity_id', 'member_id', 'unit_id', 'booked_by', 'booking_date', 'start_time', 'end_time',
        'guests', 'purpose', 'charge', 'status', 'decided_by', 'decided_at', 'decision_note', 'maintenance_bill_id',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'booking_date' => 'date',
            'decided_at' => 'datetime',
            'charge' => 'decimal:2',
            'guests' => 'integer',
        ];
    }

    public function society(): BelongsTo
    {
        return $this->belongsTo(Society::class);
    }

    public function amenity(): BelongsTo
    {
        return $this->belongsTo(Amenity::class);
    }

    public function member(): BelongsTo
    {
        return $this->belongsTo(Member::class);
    }

    public function unit(): BelongsTo
    {
        return $this->belongsTo(Unit::class);
    }

    public function bill(): BelongsTo
    {
        return $this->belongsTo(MaintenanceBill::class, 'maintenance_bill_id');
    }

    /**
     * Bookings that still hold their time slot.
     */
    public function scopeHolding(Builder $query): Builder
    {
        return $query->whereIn('status', ['pending', 'approved']);
    }

    public function timeLabel(): string
    {
        return substr((string) $this->start_time, 0, 5).' – '.substr((string) $this->end_time, 0, 5);
    }

    public function statusLabel(): string
    {
        return self::STATUSES[$this->status][0] ?? ucfirst($this->status);
    }

    public function statusBadgeClass(): string
    {
        return self::STATUSES[$this->status][1] ?? 'badge-secondary';
    }

    protected function activityModule(): string
    {
        return 'Amenity Booking';
    }

    protected function activityIdentifier(): ?string
    {
        return $this->amenity?->name.' on '.$this->booking_date?->format('d M Y');
    }
}
