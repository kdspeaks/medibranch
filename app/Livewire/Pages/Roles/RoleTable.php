<?php

namespace App\Livewire\Pages\Roles;

use Livewire\Component;
use Spatie\Permission\Models\Role;
use Illuminate\Database\Eloquent\Collection;

class RoleTable extends Component
{
    public Collection $roles;
    
    public function mount() {
        $this->roles = Role::all(); // Fetch all roles from the database
    }
    
    public function render()
    {
        return view('livewire.pages.roles.role-table');
    }
}
