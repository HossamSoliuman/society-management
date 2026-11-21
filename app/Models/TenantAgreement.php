<?php

namespace App\Models;

use App\Models\Concerns\BelongsToSociety;
use Database\Factories\TenantAgreementFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

class TenantAgreement extends Model
{
    use BelongsToSociety;

    /** @use HasFactory<TenantAgreementFactory> */
    use HasFactory;

    /** Days before the end date an agreement counts as "expiring". */
    public const EXPIRING_WITHIN_DAYS = 30;

    protected $fillable = [
        'society_id', 'tenant_id', 'unit_id', 'agreement_number', 'start_date', 'end_date',
        'monthly_rent', 'security_deposit', 'document_path', 'status', 'terminated_at', 'notes',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'start_date' => 'date',
            'end_date' => 'date',
            'terminated_at' => 'datetime',
            'monthly_rent' => 'decimal:2',
            'security_deposit' => 'decimal:2',
        ];
    }

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class)->withTrashed();
    }

    public function unit(): BelongsTo
    {
        return $this->belongsTo(Unit::class);
    }

    /**
     * Active agreements whose end date falls within the next $days days.
     */
    public function scopeExpiringWithin(Builder $query, int $days = self::EXPIRING_WITHIN_DAYS): Builder
    {
        return $query->where('status', 'active')
            ->whereDate('end_date', '>=', Carbon::today())
            ->whereDate('end_date', '<=', Carbon::today()->addDays($days));
    }

    public function scopeLapsed(Builder $query): Builder
    {
        return $query->where('status', 'active')->whereDate('end_date', '<', Carbon::today());
    }

    /**
     * active | expiring | expired | terminated, derived from status and dates.
     */
    public function state(): string
    {
        if ($this->status === 'terminated') {
            return 'terminated';
        }

        $today = Carbon::today();

        return match (true) {
            $this->end_date->lt($today) => 'expired',
            $this->end_date->lte($today->copy()->addDays(self::EXPIRING_WITHIN_DAYS)) => 'expiring',
            default => 'active',
        };
    }

    public function stateLabel(): string
    {
        return match ($this->state()) {
            'expiring' => 'Expiring in '.max(0, (int) Carbon::today()->diffInDays($this->end_date)).' days',
            default => ucfirst($this->state()),
        };
    }

    public function stateBadgeClass(): string
    {
        return match ($this->state()) {
            'active' => 'badge-success',
            'expiring' => 'badge-warning',
            'expired' => 'badge-danger',
            default => 'badge-secondary',
        };
    }
}
