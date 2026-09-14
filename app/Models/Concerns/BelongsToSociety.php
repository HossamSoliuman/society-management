<?php

namespace App\Models\Concerns;

use App\Models\Society;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\Relation;

/**
 * Tenant ownership for society-panel models.
 *
 * No global scope is registered on purpose (super-admin screens and console
 * commands need cross-society access); instead every society-panel query
 * should call {@see scopeForSociety()} and route-model binding is constrained
 * to the signed-in user's society so a foreign row resolves to a 404.
 */
trait BelongsToSociety
{
    /**
     * Constrain a query to rows owned by the given society.
     */
    public function scopeForSociety(Builder $query, Society|int $society): Builder
    {
        $societyId = $society instanceof Society ? $society->id : $society;

        return $query->where($this->qualifyColumn('society_id'), $societyId);
    }

    public function resolveRouteBindingQuery($query, $value, $field = null): Relation|Builder
    {
        $query = parent::resolveRouteBindingQuery($query, $value, $field);
        $user = auth()->user();

        if (! $user || $user->hasRole('super_admin')) {
            return $query;
        }

        if (! $user->society_id) {
            return $query->whereRaw('1 = 0');
        }

        return $query->where($this->qualifyColumn('society_id'), $user->society_id);
    }
}
