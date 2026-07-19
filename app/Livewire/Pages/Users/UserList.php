<?php

namespace App\Livewire\Pages\Users;

use App\Models\User;
use Filament\Actions\Action;
use Filament\Actions\Concerns\InteractsWithActions;
use Filament\Actions\Contracts\HasActions;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\CheckboxList;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Schemas\Components\Group;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ViewColumn;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Table;
use Livewire\Component;
use Spatie\Permission\Models\Role;

class UserList extends Component implements HasActions, HasForms, HasTable
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
                    ->label(__('messages.name'))
                    ->required()
                    ->maxLength(255),
                TextInput::make('email')
                    ->unique(ignoreRecord: true)
                    ->required()
                    ->maxLength(255),
                TextInput::make('password')
                    ->required(fn () => ! $isEdit)
                    ->maxLength(255)
                    ->password()
                    ->revealable()
                    ->dehydrated(fn ($state) => filled($state)),

                Select::make('roles')
                    ->relationship('roles', 'name')
                    ->multiple()
                    ->preload()
                    ->label(__('messages.roles'))
                    ->required()
                    ->native(false),

                Select::make('branches')
                    ->relationship('branches', 'name')
                    ->multiple()
                    ->preload()
                    ->label(__('messages.branches'))
                    ->native(false),

            ]),
        ];
    }

    public function createAction(): Action
    {
        return CreateAction::make('create')
            ->model(User::class)
            ->label(__('messages.create_user'))
            ->modalHeading(__('messages.create_new_user'))
            ->schema($this->getFormSchema());
    }

    public function table(Table $table): Table
    {
        return $table
            ->query(User::query())

            ->columns([
                ViewColumn::make('name')
                    ->label(__('messages.name'))
                    ->view('components.datatable.user_name')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('email')
                    ->label(__('messages.email')),
                TextColumn::make('roles.name')
                    ->label(__('messages.roles'))
                    ->sortable(),
                TextColumn::make('branches.name')
                    ->label(__('messages.branches'))
                    ->badge()
                    ->separator(','),
            ])
            ->filters([
                // ...
            ])
            ->recordActions([
                EditAction::make()
                    ->modalHeading(__('messages.edit_user'))
                    ->visible(fn ($record) => ! $record->roles->contains('name', 'Super Admin'))
                    ->schema($this->getFormSchema(true)),
                DeleteAction::make()
                    ->visible(fn ($record) => ! $record->roles->contains('name', 'Super Admin'))
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
        return view('livewire.pages.users.user-list');
    }
}
