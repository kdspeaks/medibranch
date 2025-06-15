<?php

namespace App\Livewire\Components\Ui\Modal;

use Livewire\Component;
use Livewire\Attributes\On;
use Livewire\Attributes\Validate;
use Spatie\Permission\Models\Permission;

class CreatePermission extends Base
{
    #[Validate('required|string|min:3|unique:Spatie\Permission\Models\Permission,name')]
    public string $name;
    public Permission $permission;
    public bool $isEdit = false;
    
    #[On('create-permission')]
    public function showCreateModal(): void
    {
        $this->reset(['name', 'isEdit', 'permission']);
        $this->show = true;
    }

    #[On('edit-permission')]
    public function showEditModal(int $id): void
    {
        $this->setPermission($id);
        $this->isEdit = true;
        $this->show = true;
    }
    
    public function setPermission(int $id): void
    {
        $this->permission = Permission::findOrFail($id);
        $this->name = $this->permission->name;
    }

    public function save() {
        $this->validate();
        if ($this->isEdit)
            $this->permission->update(['name' => $this->name]);
        else
            Permission::create(['name' => $this->name]);

        $this->reset('name');
        $this->dispatch('pg:eventRefresh-PermissionTable');
        $this->show = false;
    }
    public function render()
    {
        return view('livewire.components.ui.modal.create-permission');
    }
}
