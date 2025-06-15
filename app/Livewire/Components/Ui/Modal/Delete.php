<?php

namespace App\Livewire\Components\Ui\Modal;

use Illuminate\Database\Eloquent\Model;
use Livewire\Component;
use Livewire\Attributes\On;

class Delete extends Base
{
    public int $id;
    public string $model;
    public Model $entity;

    public function setEntity(int $id, string $model): void
    {
        $this->id = $id;
        $this->model = $model;
        $this->entity = app($model)::findOrFail($id);
    }
    public function delete(): void
    {
        $this->entity->delete();
        $this->dispatch('pg:eventRefresh-PermissionTable');
        $this->show = false;
    }
    #[On('delete-modal')]
    public function showCreateModal(int $id, string $model): void
    {
        $this->setEntity($id, $model);
        $this->show = true;
    }
    public function render()
    {
        return view('livewire.components.ui.modal.delete');
    }
}
