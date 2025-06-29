<?php

namespace App\Livewire\Pages\Users;

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

#[Title('Users')]
class UserList extends Component
{
    public function render()
    {
        return view('livewire.pages.users.user-list');
    }

}
