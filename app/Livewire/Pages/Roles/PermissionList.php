<?php

namespace App\Livewire\Pages\Roles;

use Filament\Actions\EditAction;
use Filament\Actions\DeleteAction;
use Livewire\Component;
use Filament\Tables\Table;
use Filament\Actions\Action;
use Livewire\Attributes\Title;
use Filament\Actions\CreateAction;
use Filament\Forms\Contracts\HasForms;
use Filament\Tables\Columns\TextColumn;
use Filament\Forms\Components\TextInput;
use Spatie\Permission\Models\Permission;
use Filament\Actions\Contracts\HasActions;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Actions\Concerns\InteractsWithActions;
use Filament\Tables\Contracts\HasTable;

#[Title('Permission List')]
class PermissionList extends Component implements HasForms, HasActions, HasTable
{
    use InteractsWithActions;
    use InteractsWithTable;
    use InteractsWithForms;
    public function render()
    {
        return view('livewire.pages.roles.permission-list');
    }

    public function createAction(): Action
    {
       return CreateAction::make('create')
       ->modalHeading("Create New Permission")
        ->model(Permission::class)
        ->label('Create Permission')
        ->schema([
                    TextInput::make('name')
                    ->model(Permission::class)
                        ->required()
                        ->maxLength(255),
                    // ...
        ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->query(Permission::query())
            
            ->columns([
                TextColumn::make('name')
                ->searchable()
                ->sortable(),
            ])
            ->filters([
                // ...
            ])
            ->recordActions([
                EditAction::make()
                ->modalHeading('Edit Permission')
                ->schema([
                    TextInput::make('name')
                        ->required()
                        ->maxLength(255),
                    // ...
                ]),
                DeleteAction::make()
                ->requiresConfirmation()
            ])
            ->toolbarActions([
                // ...
            ])
            ->headerActions([
                
            ])
            ->paginated([10, 20, 50, 100, 'all'])
             ->defaultPaginationPageOption(20);;
    }
}
