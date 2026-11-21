<?php

namespace App\Models;

use App\Models\Concerns\BelongsToSociety;
use Database\Factories\VisitorPreApprovalFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

class VisitorPreApproval extends Model
{
    use BelongsToSociety;

    /** @use HasFactory<VisitorPreApprovalFactory> */
    use HasFactory;

    public const STATUSES = [
        'pending' => ['Expected', 'badge-info'],
        'checked_in' => ['Arrived', 'badge-success'],
        'expired' => ['Expired', 'badge-secondary'],
        'cancelled' => ['Cancelled', 'badge-danger'],
    ];

    protected $fillable = [
        'society_id', 'unit_id', 'member_id', 'created_by', 'visitor_name', 'mobile', 'purpose',
        'people_count', 'vehicle_no', 'expected_at', 'valid_until', 'pass_code', 'status', 'notes',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'expected_at' => 'datetime',
            'valid_until' => 'datetime',
            'people_count' => 'integer',
        ];
    }

    public function society(): BelongsTo
    {
        return $this->belongsTo(Society::class);
    }

    public function unit(): BelongsTo
    {
        return $this->belongsTo(Unit::class);
    }

    public function member(): BelongsTo
    {
        return $this->belongsTo(Member::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function visit(): HasOne
    {
        return $this->hasOne(Visitor::class, 'pre_approval_id');
    }

    /**
     * Passes that can still be used at the gate right now.
     */
    public function scopeUsable(Builder $query): Builder
    {
        return $query->where('status', 'pending')->where('valid_until', '>=', now());
    }

    /**
     * A 6-digit code not currently held by another usable pass in the society.
     */
    public static function freshCode(int $societyId): string
    {
        do {
            $code = str_pad((string) random_int(0, 999999), 6, '0', STR_PAD_LEFT);
        } while (static::query()->where('society_id', $societyId)->where('pass_code', $code)->usable()->exists());

        return $code;
    }

    /**
     * Status shown to people: a pending pass past its window reads as expired.
     */
    public function displayStatus(): string
    {
        return $this->status === 'pending' && $this->valid_until->isPast() ? 'expired' : $this->status;
    }

    public function statusLabel(): string
    {
        return self::STATUSES[$this->displayStatus()][0];
    }

    public function statusBadgeClass(): string
    {
        return self::STATUSES[$this->displayStatus()][1];
    }

    public function purposeLabel(): string
    {
        return Visitor::PURPOSES[$this->purpose][0] ?? ucfirst($this->purpose);
    }
}
