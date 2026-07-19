<?php

namespace App\Livewire\Pages\Roles;

use Filament\Actions\Action;
use Filament\Actions\Concerns\InteractsWithActions;
use Filament\Actions\Contracts\HasActions;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Table;
use Livewire\Attributes\Title;
use Livewire\Component;
use Spatie\Permission\Models\Permission;

#[Title('Permission List')]
class PermissionList extends Component implements HasActions, HasForms, HasTable
{
    use InteractsWithActions;
    use InteractsWithForms;
    use InteractsWithTable;

    public function render()
    {
        return view('livewire.pages.roles.permission-list');
    }

    protected function getFormSchema(): array
    {
        return [
            TextInput::make('name')
                ->label(__('messages.name'))
                ->required()
                ->maxLength(255),
        ];
    }

    public function createAction(): Action
    {
        return CreateAction::make('create')
            ->modalHeading(__('messages.create_new_permission'))
            ->model(Permission::class)
            ->label(__('messages.create_permission'))
            ->schema($this->getFormSchema());
    }

    public function table(Table $table): Table
    {
        return $table
            ->query(Permission::query())

            ->columns([
                TextColumn::make('name')
                    ->label(__('messages.name'))
                    ->searchable()
                    ->sortable(),
            ])
            ->filters([
                // ...
            ])
            ->recordActions([
                EditAction::make()
                    ->modalHeading(__('messages.edit_permission'))
                    ->schema($this->getFormSchema()),
                DeleteAction::make()
                    ->requiresConfirmation(),
            ])
            ->toolbarActions([
                // ...
            ])
            ->headerActions([

            ])
            ->paginated([10, 20, 50, 100, 'all'])
            ->defaultPaginationPageOption(20);
    }
}
