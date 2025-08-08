<?php

namespace App\Livewire\Pages\Supplier;

use App\Models\Supplier;
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

class SupplierList extends Component implements HasForms, HasActions, HasTable
{
    use InteractsWithActions;
    use InteractsWithTable;
    use InteractsWithForms;

    public function createAction(): Action
    {
        return CreateAction::make('create')
            ->modalHeading("Create New Supplier")
            ->model(Supplier::class)
            ->label('Create Supplier')
            ->form([
                Group::make()
                    ->columns(2)
                    ->schema([
                        TextInput::make('name')
                            ->label('Supplier Name')
                            ->required()
                            ->maxLength(255)
                            ->placeholder('Enter supplier name'),

                        TextInput::make('contact_person')
                            ->label('Contact Person')
                            ->maxLength(255)
                            ->placeholder('Enter contact person name'),

                        TextInput::make('email')
                            ->label('Email')
                            ->email()
                            ->maxLength(255)
                            ->placeholder('Enter email address'),

                        TextInput::make('phone')
                            ->label('Phone')
                            ->tel()
                            ->maxLength(20)
                            ->placeholder('Enter phone number'),
                    ]),

                Group::make()
                    ->columns(2)
                    ->schema([
                        TextInput::make('address')
                            ->label('Address')
                            ->maxLength(255)
                            ->placeholder('Street address'),

                        TextInput::make('city')
                            ->label('City')
                            ->maxLength(255),

                        TextInput::make('state')
                            ->label('State')
                            ->maxLength(255),

                        TextInput::make('country')
                            ->label('Country')
                            ->maxLength(255),

                        TextInput::make('postal_code')
                            ->label('Postal Code')
                            ->maxLength(20),
                    ]),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->query(Supplier::query())

            ->columns([
                TextColumn::make('name')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('contact_person')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('email')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('phone')
                    ->searchable()
                    ->sortable(),

            ])
            ->filters([
                // ...
            ])
            ->actions([
                EditAction::make()
                    ->modalHeading('Edit Supplier')
                    ->form([
                        Group::make()
                            ->columns(2)
                            ->schema([
                                TextInput::make('name')
                                    ->label('Supplier Name')
                                    ->required()
                                    ->maxLength(255)
                                    ->placeholder('Enter supplier name'),

                                TextInput::make('contact_person')
                                    ->label('Contact Person')
                                    ->maxLength(255)
                                    ->placeholder('Enter contact person name'),

                                TextInput::make('email')
                                    ->label('Email')
                                    ->email()
                                    ->maxLength(255)
                                    ->placeholder('Enter email address'),

                                TextInput::make('phone')
                                    ->label('Phone')
                                    ->tel()
                                    ->maxLength(20)
                                    ->placeholder('Enter phone number'),
                            ]),

                        Group::make()
                            ->columns(2)
                            ->schema([
                                TextInput::make('address')
                                    ->label('Address')
                                    ->maxLength(255)
                                    ->placeholder('Street address'),

                                TextInput::make('city')
                                    ->label('City')
                                    ->maxLength(255),

                                TextInput::make('state')
                                    ->label('State')
                                    ->maxLength(255),

                                TextInput::make('country')
                                    ->label('Country')
                                    ->maxLength(255),

                                TextInput::make('postal_code')
                                    ->label('Postal Code')
                                    ->maxLength(20),
                            ]),
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

    public function render()
    {
        return view('livewire.pages.supplier.supplier-list');
    }
}
