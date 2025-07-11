<?php

namespace App\Livewire\Pages\Medicines;

use App\Models\Medicine;
use Livewire\Component;
use Filament\Tables\Table;
use Filament\Actions\Action;
use Livewire\Attributes\Title;
use Filament\Actions\CreateAction;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Roles;
use Filament\Forms\Components\Group;
use Filament\Forms\Contracts\HasForms;
use Filament\Tables\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Contracts\HasTable;
use Filament\Forms\Components\TextInput;
use Spatie\Permission\Models\Permission;
use Filament\Tables\Actions\DeleteAction;
use Filament\Actions\Contracts\HasActions;
use Filament\Forms\Components\CheckboxList;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Actions\Concerns\InteractsWithActions;
use Filament\Tables\Actions\CreateAction as ActionsCreateAction;

class MedicineList extends Component implements HasForms, HasActions, HasTable
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
            ->model(Medicine::class)
            ->label('Create Role')
            ->modalHeading('Create New Role')
            ->form([
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
            ->query(Medicine::query())

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
            ->actions([
                EditAction::make()
                    ->modalHeading('Edit Permission')
                    ->visible(fn($record) => $record->name !== 'Super Admin')
                    ->form([
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
            ->bulkActions([
                // ...
            ])
            ->headerActions([])
            ->paginated([10, 20, 50, 100, 'all'])
            ->defaultPaginationPageOption(20);;
    }

    public function render()
    {
        return view('livewire.pages.medicines.medicine-list');
    }
}
