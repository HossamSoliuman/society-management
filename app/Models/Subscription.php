<?php

namespace App\Models;

use App\Observers\SubscriptionObserver;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Carbon;

#[ObservedBy(SubscriptionObserver::class)]
class Subscription extends Model
{
    use HasFactory, SoftDeletes;

    /** Days before end_date at which a subscription is flagged as expiring. */
    public const EXPIRING_SOON_DAYS = 30;

    protected $fillable = [
        'subscription_number', 'society_id', 'plan_id', 'renewed_from_id', 'building_name',
        'monthly_cost_per_flat', 'amount', 'start_date', 'end_date', 'additional_free_days',
        'billing_cycle', 'description', 'status', 'cancelled_at', 'cancel_reason',
        'payment_method', 'payment_date', 'payment_proof', 'reference_number', 'notes',
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
        'payment_date' => 'date',
        'cancelled_at' => 'datetime',
        'monthly_cost_per_flat' => 'decimal:2',
        'amount' => 'decimal:2',
    ];

    public function society(): BelongsTo
    {
        return $this->belongsTo(Society::class);
    }

    public function plan(): BelongsTo
    {
        return $this->belongsTo(SubscriptionPlan::class, 'plan_id');
    }

    public function renewedFrom(): BelongsTo
    {
        return $this->belongsTo(self::class, 'renewed_from_id');
    }

    public function renewals(): HasMany
    {
        return $this->hasMany(self::class, 'renewed_from_id');
    }

    public function invoices(): HasMany
    {
        return $this->hasMany(Invoice::class);
    }

    /**
     * Subscriptions that still grant access (not cancelled / expired).
     */
    public function scopeCurrent(Builder $query): Builder
    {
        return $query->whereIn('status', ['active', 'expiring_soon']);
    }

    /**
     * Whole days until end_date (negative once past).
     */
    public function daysUntilExpiry(?Carbon $asOf = null): int
    {
        return (int) ($asOf ?? Carbon::today())->startOfDay()->diffInDays($this->end_date->copy()->startOfDay(), false);
    }

    /**
     * Last calendar day on which the society may still sign in.
     */
    public function accessEndsAt(): Carbon
    {
        $grace = (int) ($this->society?->grace_period_days ?? 0) + (int) $this->additional_free_days;

        return $this->end_date->copy()->addDays($grace)->endOfDay();
    }

    /**
     * Status this row should hold on the given day, ignoring cancellation.
     */
    public function computeStatus(?Carbon $asOf = null): string
    {
        if ($this->status === 'cancelled') {
            return 'cancelled';
        }

        $asOf ??= Carbon::today();

        if ($asOf->greaterThan($this->accessEndsAt())) {
            return 'expired';
        }

        return $this->daysUntilExpiry($asOf) <= self::EXPIRING_SOON_DAYS ? 'expiring_soon' : 'active';
    }

    public function statusBadgeClass(): string
    {
        return match ($this->status) {
            'active' => 'badge-success',
            'expiring_soon' => 'badge-warning',
            'expired' => 'badge-danger',
            'cancelled' => 'badge-secondary',
            default => 'badge-secondary',
        };
    }

    public function statusLabel(): string
    {
        return ucwords(str_replace('_', ' ', $this->status));
    }
}
