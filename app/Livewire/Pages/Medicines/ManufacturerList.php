<?php

namespace App\Livewire\Pages\Medicines;

use Filament\Schemas\Components\Group;
use Filament\Actions\EditAction;
use Filament\Actions\DeleteAction;
use Livewire\Component;
use Filament\Tables\Table;
use App\Models\Manufacturer;
use Filament\Actions\Action;
use Livewire\Attributes\Title;
use Filament\Actions\CreateAction;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Roles;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Components\Section;
use Filament\Forms\Contracts\HasForms;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Contracts\HasTable;
use Filament\Forms\Components\TextInput;
use Spatie\Permission\Models\Permission;
use Filament\Tables\Columns\ToggleColumn;
use Filament\Actions\Contracts\HasActions;
use Filament\Forms\Components\CheckboxList;
use Filament\Forms\Components\ToggleButtons;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Actions\Concerns\InteractsWithActions;
use Filament\Tables\Actions\CreateAction as ActionsCreateAction;
use Symfony\Component\Intl\Countries;

class ManufacturerList extends Component implements HasForms, HasActions, HasTable
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
            ->model(Manufacturer::class)
            ->label(__('messages.create_manufacturer'))
            ->modalHeading(__('messages.create_new_manufacturer'))
            ->schema([
                Group::make()
                    ->schema([
                        TextInput::make('name')
                            ->label(__('messages.manufacturer_name'))
                            ->required()
                            ->maxLength(255),

                        TextInput::make('contact_name')
                            ->label(__('messages.contact_person'))
                            ->maxLength(255),

                        TextInput::make('phone')
                            ->label(__('messages.phone'))
                            ->tel()
                            ->maxLength(20),

                        TextInput::make('email')
                            ->label(__('messages.email'))
                            ->email()
                            ->maxLength(255),

                        TextInput::make('address')
                            ->label(__('messages.address'))
                            ->maxLength(255),

                        TextInput::make('website')
                            ->label(__('messages.website'))
                            ->url()
                            ->maxLength(255),

                        Select::make('country')
                            ->label(__('messages.country'))
                            ->options(
                                collect(Countries::getNames('en'))->sort()->toArray()
                            )
                            ->searchable()
                            ->preload()
                            ->placeholder(__('messages.select_country'))
                            ->nullable(),

                        ToggleButtons::make('is_active')
                            ->label(__('messages.is_active'))
                            ->boolean()
                            ->inline()
                            ->default(true),
                    ])
                    ->columns(2)
                    ->columnSpanFull()
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->query(Manufacturer::query())

            ->columns([
                TextColumn::make('name')
                    ->label(__('messages.manufacturer_name'))
                    ->searchable()
                    ->sortable(),
                TextColumn::make('contact_name')
                    ->label(__('messages.contact_person'))
                    ->searchable(),
                TextColumn::make('phone')
                    ->label(__('messages.phone'))
                    ->searchable(),
                TextColumn::make('email')
                    ->label(__('messages.email'))
                    ->searchable(),
                ToggleColumn::make('is_active')
                    ->label(__('messages.is_active'))
                    ->onIcon('heroicon-m-check-circle')
                    ->offIcon('heroicon-m-x-circle')
                    ->toggleable()
                    ->default(true)
                    ->sortable()
            ])
            ->filters([
                // ...
            ])
            ->recordActions([
                EditAction::make()
                    ->modalHeading(__('messages.edit_manufacturer'))
                    ->schema([
                        Group::make()
                            ->schema([
                                TextInput::make('name')
                                    ->label(__('messages.manufacturer_name'))
                                    ->required()
                                    ->maxLength(255),

                                TextInput::make('contact_name')
                                    ->label(__('messages.contact_person'))
                                    ->maxLength(255),

                                TextInput::make('phone')
                                    ->label(__('messages.phone'))
                                    ->tel()
                                    ->maxLength(20),

                                TextInput::make('email')
                                    ->label(__('messages.email'))
                                    ->email()
                                    ->maxLength(255),

                                TextInput::make('address')
                                    ->label(__('messages.address'))
                                    ->maxLength(255),

                                TextInput::make('website')
                                    ->label(__('messages.website'))
                                    ->url()
                                    ->maxLength(255),

                                Select::make('country')
                                    ->label(__('messages.country'))
                                    ->options(
                                        collect(Countries::getNames('en'))->sort()->toArray()
                                    )
                                    ->searchable()
                                    ->preload()
                                    ->placeholder(__('messages.select_country'))
                                    ->nullable(),

                                ToggleButtons::make('is_active')
                                    ->label(__('messages.is_active'))
                                    ->boolean()
                                    ->inline()
                                    ->default(true),
                            ])
                            ->columns(2)
                            ->columnSpanFull()
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
            ->defaultPaginationPageOption(20)
            ->striped();
    }

    public function render()
    {
        return view('livewire.pages.medicines.manufacturer-list');
    }
}
