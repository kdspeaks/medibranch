<?php

namespace App\Policies;

use App\Models\Purchase;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class PurchasePolicy
{
    use HandlesAuthorization;

    public function viewAny(User $user): bool
    {
        return clone $user->can('manage-purchases');
    }

    public function view(User $user, Purchase $purchase): bool
    {
        if (!$user->can('manage-purchases')) {
            return false;
        }
        return $user->canAccessBranch($purchase->branch_id);
    }

    public function create(User $user): bool
    {
        return $user->can('manage-purchases');
    }

    public function update(User $user, Purchase $purchase): bool
    {
        if (!$user->can('manage-purchases')) {
            return false;
        }
        return $user->canAccessBranch($purchase->branch_id) && $purchase->status !== 'received';
    }

    public function delete(User $user, Purchase $purchase): bool
    {
        if (!$user->can('manage-purchases')) {
            return false;
        }
        return $user->canAccessBranch($purchase->branch_id) && $purchase->status !== 'received';
    }
}
