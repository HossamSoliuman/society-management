<?php

namespace App\Models;

use App\Models\Concerns\BelongsToSociety;
use Database\Factories\VisitorFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Visitor extends Model
{
    use BelongsToSociety;

    /** @use HasFactory<VisitorFactory> */
    use HasFactory;

    public const PURPOSES = [
        'guest' => ['Guest', 'fa-user-group'],
        'delivery' => ['Delivery', 'fa-box'],
        'cab' => ['Cab / Taxi', 'fa-taxi'],
        'service' => ['Service / Repair', 'fa-screwdriver-wrench'],
        'domestic_help' => ['Domestic Help', 'fa-broom'],
        'other' => ['Other', 'fa-circle-question'],
    ];

    protected $fillable = [
        'society_id', 'unit_id', 'member_id', 'pre_approval_id', 'name', 'mobile', 'purpose', 'company',
        'people_count', 'vehicle_no', 'check_in_at', 'check_out_at', 'checked_in_by', 'checked_out_by', 'notes',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'check_in_at' => 'datetime',
            'check_out_at' => 'datetime',
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

    public function preApproval(): BelongsTo
    {
        return $this->belongsTo(VisitorPreApproval::class, 'pre_approval_id');
    }

    public function checkedInBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'checked_in_by');
    }

    public function scopeInside(Builder $query): Builder
    {
        return $query->whereNull('check_out_at');
    }

    public function isInside(): bool
    {
        return $this->check_out_at === null;
    }

    public function purposeLabel(): string
    {
        return self::PURPOSES[$this->purpose][0] ?? ucfirst($this->purpose);
    }

    public function purposeIcon(): string
    {
        return self::PURPOSES[$this->purpose][1] ?? 'fa-user';
    }

    public function durationLabel(): string
    {
        $end = $this->check_out_at ?? now();
        $minutes = (int) $this->check_in_at->diffInMinutes($end);

        return $minutes >= 60 ? intdiv($minutes, 60).'h '.($minutes % 60).'m' : $minutes.'m';
    }
}
