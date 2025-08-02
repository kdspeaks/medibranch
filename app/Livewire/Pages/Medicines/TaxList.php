<?php

namespace App\Livewire\Pages\Medicines;

use App\Models\Tax;
use Livewire\Component;
use Filament\Tables\Table;
use Filament\Actions\Action;
use Livewire\Attributes\Title;
use Filament\Actions\CreateAction;
use Filament\Forms\Components\Group;
use Filament\Forms\Contracts\HasForms;
use Filament\Tables\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Contracts\HasTable;
use Filament\Forms\Components\TextInput;
use Spatie\Permission\Models\Permission;
use Filament\Tables\Actions\DeleteAction;
use Filament\Tables\Columns\ToggleColumn;
use Filament\Actions\Contracts\HasActions;
use Filament\Forms\Components\ToggleButtons;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Actions\Concerns\InteractsWithActions;

class TaxList extends Component implements HasForms, HasActions, HasTable
{
    use InteractsWithActions;
    use InteractsWithTable;
    use InteractsWithForms;
    public function render()
    {
        return view('livewire.pages.medicines.tax-list');
    }

    public function createAction(): Action
    {
        return CreateAction::make('create')
            ->modalHeading("Create New Tax")
            ->model(Tax::class)
            ->label('Create Tax')
            ->form([
                Group::make()
                    ->schema([
                        TextInput::make('name')
                            ->label('Name')
                            ->required()
                            ->maxLength(255),

                        TextInput::make('rate')
                            ->label('Rate')
                            ->required()
                            ->maxLength(255),



                        ToggleButtons::make('is_active')
                            ->label('Active')
                            ->boolean()
                            ->grouped()
                            ->default(true),
                    ])
                    ->columns(3)
                    ->columnSpanFull()
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->query(Tax::query())

            ->columns([
                TextColumn::make('name')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('rate')
                    ->searchable()
                    ->sortable(),
                ToggleColumn::make('is_active')
                    ->label('Active')
                    ->onIcon('heroicon-m-check-circle')
                    ->offIcon('heroicon-m-x-circle')
                    ->toggleable()
                    ->sortable()
            ])
            ->filters([
                // ...
            ])
            ->actions([
                EditAction::make()
                    ->modalHeading('Edit Tax')
                    ->form([
                        Group::make()
                            ->schema([
                                TextInput::make('name')
                                    ->label('Name')
                                    ->required()
                                    ->maxLength(255),

                                TextInput::make('rate')
                                    ->label('Rate')
                                    ->required()
                                    ->maxLength(255),



                                ToggleButtons::make('is_active')
                                    ->label('Active')
                                    ->boolean()
                                    ->grouped()
                                    ->default(true),
                            ])
                            ->columns(3)
                            ->columnSpanFull()
                    ]),
                DeleteAction::make()
                    ->requiresConfirmation()
            ])
            ->bulkActions([
                // ...
            ])
            ->headerActions([])
            ->paginated([10, 20, 50, 100, 'all'])
            ->defaultPaginationPageOption(20);;
    }
}
