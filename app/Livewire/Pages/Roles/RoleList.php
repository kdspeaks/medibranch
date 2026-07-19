<?php

namespace App\Livewire\Pages\Roles;

use Filament\Actions\Action;
use Filament\Actions\Concerns\InteractsWithActions;
use Filament\Actions\Contracts\HasActions;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\CheckboxList;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Schemas\Components\Group;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Table;
use Livewire\Component;
use Spatie\Permission\Models\Role;

class RoleList extends Component implements HasActions, HasForms, HasTable
{
    use InteractsWithActions;
    use InteractsWithForms;
    use InteractsWithTable;
    // public function createAction(): Action
    // {
    //     return CreateAction::make('create')
    //         ->model(Role::class)
    //         ->label('Create Role')
    //         ->modalHeading('Create New Role')
    //         ->form([
    //             TextInput::make('name')
    //                 ->model(Role::class)
    //                 ->required()
    //                 ->maxLength(255),
    //             CheckboxList::make('permissions')
    //                 ->relationship('permissions', 'name')
    //                 ->label('Permissions')
    //                 ->options(
    //                     \Spatie\Permission\Models\Permission::all()->pluck('name', 'id')
    //                 )
    //                 ->required()
    //         ]);
    // }

    protected function getFormSchema(bool $isEdit = false): array
    {
        return [
            Group::make([
                TextInput::make('name')
                    ->unique(ignoreRecord: true)
                    ->label(__('messages.role_name'))
                    ->required()
                    ->maxLength(255),

                CheckboxList::make('permissions')
                    ->relationship('permissions', 'name')
                    ->label(__('messages.assign_permissions'))
                    ->columns($isEdit ? 4 : 2),
            ]),
        ];
    }

    public function createAction(): Action
    {
        return CreateAction::make('create')
            ->model(Role::class)
            ->label(__('messages.create_role'))
            ->modalHeading(__('messages.create_new_role'))
            ->schema($this->getFormSchema());
    }

    public function table(Table $table): Table
    {
        return $table
            ->query(Role::query())

            ->columns([
                TextColumn::make('name')
                    ->label(__('messages.name'))
                    ->searchable()
                    ->sortable(),
                TextColumn::make('permissions.name')
                    ->label(__('messages.permissions'))
                    ->separator(', '),
            ])
            ->filters([
                // ...
            ])
            ->recordActions([
                EditAction::make()
                    ->modalHeading(__('messages.edit_role'))
                    ->visible(fn ($record) => $record->name !== 'Super Admin')
                    ->schema($this->getFormSchema(true)),
                DeleteAction::make()
                    ->visible(fn ($record) => $record->name !== 'Super Admin')
                    ->requiresConfirmation(),
            ])
            ->toolbarActions([
                // ...
            ])
            ->headerActions([])
            ->paginated([10, 20, 50, 100, 'all'])
            ->defaultPaginationPageOption(20);
    }

    public function render()
    {
        return view('livewire.pages.roles.role-list');
    }
}
