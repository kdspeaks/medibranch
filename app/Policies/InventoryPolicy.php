<?php

namespace App\Policies;

use App\Models\Inventory;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class InventoryPolicy
{
    use HandlesAuthorization;

    public function viewAny(User $user): bool
    {
        return $user->can('manage-medicines'); // Assuming inventory relies on manage-medicines for now
    }

    public function view(User $user, Inventory $inventory): bool
    {
        return $user->canAccessBranch($inventory->branch_id);
    }

    public function create(User $user): bool
    {
        return $user->can('manage-medicines');
    }

    public function update(User $user, Inventory $inventory): bool
    {
        return $user->canAccessBranch($inventory->branch_id);
    }

    public function delete(User $user, Inventory $inventory): bool
    {
        return $user->canAccessBranch($inventory->branch_id);
    }
}
