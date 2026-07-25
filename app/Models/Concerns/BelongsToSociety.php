<?php

namespace App\Models\Concerns;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\Relation;

trait BelongsToSociety
{
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
