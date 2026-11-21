<?php

namespace App\Models;

use App\Models\Concerns\BelongsToSociety;
use App\Models\Concerns\LogsActivity;
use Database\Factories\TenantFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;

class Tenant extends Model
{
    use BelongsToSociety, LogsActivity, SoftDeletes;

    /** @use HasFactory<TenantFactory> */
    use HasFactory;

    public const ID_PROOFS = ['Aadhaar', 'PAN', 'Passport', 'Driving Licence', 'Voter ID', 'Other'];

    public const VERIFICATION = ['pending' => 'Pending', 'verified' => 'Verified', 'not_required' => 'Not Required'];

    protected $fillable = [
        'society_id', 'unit_id', 'owner_member_id', 'member_id', 'name', 'mobile', 'email',
        'occupants_count', 'id_proof_type', 'id_proof_number', 'permanent_address',
        'move_in_date', 'move_out_date', 'police_verification', 'status', 'notes',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'move_in_date' => 'date',
            'move_out_date' => 'date',
            'occupants_count' => 'integer',
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

    public function owner(): BelongsTo
    {
        return $this->belongsTo(Member::class, 'owner_member_id');
    }

    public function member(): BelongsTo
    {
        return $this->belongsTo(Member::class);
    }

    public function agreements(): HasMany
    {
        return $this->hasMany(TenantAgreement::class)->latest('end_date');
    }

    public function currentAgreement(): HasOne
    {
        return $this->hasOne(TenantAgreement::class)->where('status', 'active')->latestOfMany('end_date');
    }

    public function isActive(): bool
    {
        return $this->status === 'active';
    }

    public function statusBadgeClass(): string
    {
        return $this->isActive() ? 'badge-success' : 'badge-secondary';
    }

    public function statusLabel(): string
    {
        return $this->isActive() ? 'Active' : 'Moved Out';
    }

    public function verificationBadgeClass(): string
    {
        return match ($this->police_verification) {
            'verified' => 'badge-success',
            'not_required' => 'badge-secondary',
            default => 'badge-warning',
        };
    }

    public function verificationLabel(): string
    {
        return self::VERIFICATION[$this->police_verification] ?? ucfirst($this->police_verification);
    }

    protected function activityModule(): string
    {
        return 'Tenant';
    }
}
