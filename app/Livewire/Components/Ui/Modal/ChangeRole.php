<?php

namespace App\Livewire\Components\Ui\Modal;

use App\Models\User;
use Livewire\Component;
use Spatie\Permission\Models\Role;
use Illuminate\Database\Eloquent\Collection;

class ChangeRole extends Base
{
    public ?User $user = null;
    public string $selectedRole = '';
    public Collection $roles;
    public string $name;

    public function mount($payload = null): void
    {
        if ($payload && isset($payload['userId'])) {
            $this->user = User::findOrFail($payload['userId']);
            $this->name = $this->user->name;
            $this->selectedRole = $this->user->getRoleNames()->first() ?? '';
        }

        $this->roles = Role::all(); // Used for dropdown
    }

    public function updateRole(): void
    {
        if ($this->user && $this->selectedRole) {
            $this->user->syncRoles([$this->selectedRole]);
            $this->dispatch('pg:eventRefresh-UserTable'); // Optional: Tell table to refresh
            $this->show = false; // Close the modal
            session()->flash('message', 'Role updated successfully!');
        } else {
            $this->js("alert('Select a role to update!');");
        }
    }

    public function render()
    {
        return view('livewire.components.ui.modal.change-role');
    }

    public function setUser(int $userId): void
    {
        $this->user = User::findOrFail($userId);
        $this->name = $this->user->name;
        $this->selectedRole = $this->user->getRoleNames()->first() ?? '';
    }

    #[\Livewire\Attributes\On('edit-user-modal')]
    public function showModal(int $userId): void
    {
        $this->setUser($userId);
        $this->show = true;
    }
    // #[\Livewire\Attributes\On('edit-user-modal-close')]
    // public function closeModal(int $userId): void
    // {
    //     $this->setUser($userId);
    //     $this->show = true;
    // }
}
