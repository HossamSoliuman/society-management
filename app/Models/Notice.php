<?php

namespace App\Models;

use App\Models\Concerns\LogsActivity;
use App\Models\Concerns\TargetsAudience;
use Database\Factories\NoticeFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Notice extends Model
{
    /** @use HasFactory<NoticeFactory> */
    use HasFactory, LogsActivity, TargetsAudience;

    /**
     * Audience presets for notices written by a society office.
     *
     * @var array<string, array{label: string, roles: array<int, string>|null}>
     */
    public const SOCIETY_AUDIENCES = [
        'everyone' => ['label' => 'Everyone (residents and team)', 'roles' => null],
        'residents' => ['label' => 'Residents only', 'roles' => ['member']],
        'team' => ['label' => 'Society team only', 'roles' => ['society_admin', 'manager', 'accountant', 'staff']],
    ];

    protected $fillable = [
        'society_id', 'origin', 'title', 'notice_type', 'priority', 'short_description', 'content',
        'attach_path', 'publish_at', 'expires_at', 'pin_to_dashboard',
        'audience_type', 'target_roles', 'estimated_recipients', 'delivered_count', 'delivered_at',
        'send_email', 'send_sms', 'require_acknowledgement', 'status', 'created_by',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'publish_at' => 'datetime',
            'expires_at' => 'datetime',
            'delivered_at' => 'datetime',
            'target_roles' => 'array',
            'pin_to_dashboard' => 'boolean',
            'send_email' => 'boolean',
            'send_sms' => 'boolean',
            'require_acknowledgement' => 'boolean',
        ];
    }

    public function isSocietyAuthored(): bool
    {
        return $this->origin === 'society';
    }

    /**
     * Notices a society office wrote for its own residents and team.
     */
    public function scopeAuthoredBySociety(Builder $query, Society|int $society): Builder
    {
        $societyId = $society instanceof Society ? $society->id : $society;

        return $query->where('origin', 'society')->where('society_id', $societyId);
    }

    /**
     * Key of SOCIETY_AUDIENCES matching the stored role targeting.
     */
    public function societyAudience(): string
    {
        foreach (self::SOCIETY_AUDIENCES as $key => $audience) {
            if ($audience['roles'] == $this->target_roles) {
                return $key;
            }
        }

        return 'everyone';
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function acknowledgements(): HasMany
    {
        return $this->hasMany(NoticeAcknowledgement::class);
    }

    /**
     * Published, already-live and not yet expired.
     */
    public function scopeLive(Builder $query): Builder
    {
        return $query->where('status', 'published')
            ->where(fn ($q) => $q->whereNull('publish_at')->orWhere('publish_at', '<=', now()))
            ->where(fn ($q) => $q->whereNull('expires_at')->orWhere('expires_at', '>=', now()));
    }

    public function isAcknowledgedBy(User $user): bool
    {
        return $this->relationLoaded('acknowledgements')
            ? $this->acknowledgements->contains('user_id', $user->id)
            : $this->acknowledgements()->where('user_id', $user->id)->exists();
    }

    /**
     * Notification channels: in-app always, plus mail / sms when ticked.
     *
     * @return array<int, string>
     */
    public function channels(): array
    {
        return array_values(array_filter([
            'database',
            $this->send_email ? 'mail' : null,
            $this->send_sms ? 'sms' : null,
        ]));
    }

    public function priorityBadgeClass(): string
    {
        return match ($this->priority) {
            'high' => 'badge-danger',
            'medium' => 'badge-warning',
            default => 'badge-success',
        };
    }

    public function statusBadgeClass(): string
    {
        return match ($this->status) {
            'published' => 'badge-success',
            'scheduled' => 'badge-info',
            'expired' => 'badge-danger',
            default => 'badge-secondary',
        };
    }
}
