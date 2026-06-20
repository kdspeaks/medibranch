<?php

namespace App\Models\Concerns;

use App\Models\User;
use Illuminate\Database\Eloquent\Builder;

trait BelongsToBranch
{
    public function scopeForUserBranches(Builder $query, User $user): Builder
    {
        if ($user->hasRole('Super Admin')) {
            return $query;
        }

        return $query->whereIn('branch_id', $user->branches->pluck('id'));
    }
}
