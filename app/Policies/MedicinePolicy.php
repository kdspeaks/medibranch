<?php

namespace App\Policies;

use App\Models\Medicine;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class MedicinePolicy
{
    use HandlesAuthorization;

    public function viewAny(User $user): bool
    {
        return $user->can('manage-medicines');
    }

    public function view(User $user, Medicine $medicine): bool
    {
        return $user->can('manage-medicines');
    }

    public function create(User $user): bool
    {
        return $user->can('manage-medicines');
    }

    public function update(User $user, Medicine $medicine): bool
    {
        return $user->can('manage-medicines');
    }

    public function delete(User $user, Medicine $medicine): bool
    {
        return $user->can('manage-medicines');
    }
}
