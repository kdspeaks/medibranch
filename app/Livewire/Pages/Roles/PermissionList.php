<?php

namespace App\Livewire\Pages\Roles;

use Livewire\Component;
use Filament\Actions\Action;
use Livewire\Attributes\Title;
use Filament\Actions\CreateAction;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Components\TextInput;
use Spatie\Permission\Models\Permission;
use Filament\Actions\Contracts\HasActions;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Actions\Concerns\InteractsWithActions;
use Filament\Actions\EditAction;

#[Title('Permission List')]
class PermissionList extends Component implements HasForms, HasActions
{
    use InteractsWithActions;
    use InteractsWithForms;
    public function render()
    {
        return view('livewire.pages.roles.permission-list');
    }

    public function createAction(): Action
    {
       return CreateAction::make('create')
        ->model(Permission::class)
        ->label('Create Permission')
        ->form([
                    TextInput::make('name')
                    ->model(Permission::class)
                        ->required()
                        ->maxLength(255),
                    // ...
        ]);
    }
}
