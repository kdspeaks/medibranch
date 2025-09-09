<?php

namespace App\Livewire\Pages\Roles;


use Filament\Schemas\Components\Group;
use Filament\Actions\EditAction;
use Filament\Actions\DeleteAction;
use Livewire\Component;
use Filament\Tables\Table;
use Filament\Actions\Action;
use Livewire\Attributes\Title;
use Filament\Actions\CreateAction;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Roles;
use Filament\Forms\Contracts\HasForms;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Contracts\HasTable;
use Filament\Forms\Components\TextInput;
use Spatie\Permission\Models\Permission;
use Filament\Actions\Contracts\HasActions;
use Filament\Forms\Components\CheckboxList;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Actions\Concerns\InteractsWithActions;
use Filament\Tables\Actions\CreateAction as ActionsCreateAction;

class RoleList extends Component implements HasForms, HasActions, HasTable
{
    use InteractsWithActions;
    use InteractsWithTable;
    use InteractsWithForms;
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

    public function createAction(): Action
    {
        return CreateAction::make('create')
            ->model(Role::class)
            ->label('Create Role')
            ->modalHeading('Create New Role')
            ->schema([
                Group::make([
                    TextInput::make('name')
                        ->unique(ignoreRecord: true)
                        ->label('Role Name')
                        ->required()
                        ->maxLength(255),

                    CheckboxList::make('permissions')
                        // ->required()
                        ->relationship('permissions', 'name')
                        ->label('Assign Permissions')
                        ->options(
                            Permission::all()->pluck('name', 'id')
                        )
                        ->columns(2)



                ])
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->query(Role::query())

            ->columns([
                TextColumn::make('name')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('permissions.name')
                    ->separator(', ')
            ])
            ->filters([
                // ...
            ])
            ->recordActions([
                EditAction::make()
                    ->modalHeading('Edit Permission')
                    ->visible(fn($record) => $record->name !== 'Super Admin')
                    ->schema([
                        TextInput::make('name')
                            ->required()
                            ->unique(ignoreRecord: true)
                            ->maxLength(255),
                        CheckboxList::make('permissions')
                            ->relationship('permissions', 'name')
                            ->label('Assign Permissions')
                            // ->required()
                            ->columns(4),
                    ]),
                DeleteAction::make()
                    ->visible(fn($record) => $record->name !== 'Super Admin')
                    ->requiresConfirmation()
            ])
            ->toolbarActions([
                // ...
            ])
            ->headerActions([])
            ->paginated([10, 20, 50, 100, 'all'])
            ->defaultPaginationPageOption(20);;
    }

    public function render()
    {
        return view('livewire.pages.roles.role-list');
    }
}
