<?php

namespace App\Models\Concerns;

use App\Models\Society;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Shared audience targeting for announcements and notices:
 * `society_id` null = every society, `target_roles` null = every society-panel
 * role (including portal members).
 */
trait TargetsAudience
{
    /**
     * Roles that can receive society-facing communications.
     *
     * @var array<int, string>
     */
    public static array $audienceRoles = ['society_admin', 'manager', 'accountant', 'staff', 'member'];

    public function society(): BelongsTo
    {
        return $this->belongsTo(Society::class);
    }

    /**
     * Query of the users this item should reach.
     *
     * @return Builder<User>
     */
    public function recipientsQuery(): Builder
    {
        $roles = $this->target_roles ?: static::$audienceRoles;

        return User::query()
            ->where('status', 'active')
            ->whereNotNull('society_id')
            ->when($this->society_id, fn ($q) => $q->where('society_id', $this->society_id))
            ->whereHas('roles', fn ($q) => $q->where('roles.status', 'active')->whereIn('roles.name', $roles));
    }

    public function countRecipients(): int
    {
        return $this->recipientsQuery()->count();
    }

    /**
     * Rows visible to a society: global ones or those targeted at it.
     */
    public function scopeVisibleToSociety(Builder $query, Society|int $society): Builder
    {
        $societyId = $society instanceof Society ? $society->id : $society;

        return $query->where(fn ($q) => $q->whereNull($this->qualifyColumn('society_id'))->orWhere($this->qualifyColumn('society_id'), $societyId));
    }

    /**
     * Rows whose role targeting includes the given user.
     */
    public function scopeForUser(Builder $query, User $user): Builder
    {
        $roleNames = $user->roles->pluck('name')->all();

        return $query->visibleToSociety((int) $user->society_id)
            ->where(function ($q) use ($roleNames) {
                $q->whereNull($this->qualifyColumn('target_roles'));
                foreach ($roleNames as $role) {
                    $q->orWhereJsonContains($this->qualifyColumn('target_roles'), $role);
                }
            });
    }

    public function audienceLabel(): string
    {
        $scope = $this->society_id ? ($this->society?->name ?? 'One society') : 'All societies';
        $roles = $this->target_roles ? collect($this->target_roles)->map(fn ($r) => ucwords(str_replace('_', ' ', $r)))->implode(', ') : 'All roles';

        return "{$scope} · {$roles}";
    }
}
