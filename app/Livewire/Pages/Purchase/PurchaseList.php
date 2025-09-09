<?php

namespace App\Livewire\Pages\Purchase;

use Filament\Schemas\Components\Group;
use Filament\Actions\DeleteAction;
use App\Models\Tax;
use Livewire\Component;
use App\Models\Medicine;
use App\Models\Supplier;
use Filament\Tables\Table;
use Filament\Actions\Action;
use Livewire\Attributes\Title;
use Filament\Actions\CreateAction;
use Illuminate\Support\Facades\Auth;
use Filament\Forms\Contracts\HasForms;
use Filament\Tables\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ViewColumn;
use Filament\Tables\Contracts\HasTable;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Spatie\Permission\Models\Permission;
use Filament\Tables\Columns\ToggleColumn;
use Filament\Actions\Contracts\HasActions;
use Filament\Forms\Components\CheckboxList;
use Filament\Forms\Components\ToggleButtons;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Actions\Concerns\InteractsWithActions;

class PurchaseList extends Component implements HasForms, HasActions, HasTable
{
    use InteractsWithActions;
    use InteractsWithTable;
    use InteractsWithForms;

    public function createAction(): Action
    {
        return CreateAction::make('create')
            ->model(Medicine::class)
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
            ->query(Medicine::query())
            ->columns([
                ViewColumn::make('name')
                    ->view('components.datatable.medicine_name')
                    ->searchable(['name', 'sku'])
                    ->sortable(),
                // TextColumn::make('name')
                //     ->searchable()
                //     ->sortable(),

                TextColumn::make('potency')
                    ->separator(', '),
                TextColumn::make('form')
                    ->separator(', '),
                TextColumn::make('barcode')
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('packing_info')
                    ->label('Packing')
                    ->state(fn($record) => "{$record->packing_quantity}{$record->packing_unit}"),
                TextColumn::make('price_info')
                    ->label('Last Updated Price')
                    ->view('components.datatable.medicine_price'),
                TextColumn::make('tax.name')
                    ->separator(', '),
                ToggleColumn::make('is_active')
                    ->label('Active')
                    ->onIcon('heroicon-m-check-circle')
                    ->offIcon('heroicon-m-x-circle')
                    ->toggleable()
                    ->sortable()
                    ->visible(Auth::user()?->can('manage_medicines'))
                    ->afterStateUpdated(function ($record, $state) {
                        // Runs after the state is saved to the database.
                        Notification::make()
                            ->title('Medicine Updated')
                            ->body('Medicine has been successfully updated.')
                            ->success()
                            ->send();
                    }),

            ])
            ->filters([
                // ...
            ])
            ->recordActions([
                Action::make('edit')
                    ->icon('heroicon-m-pencil-square')
                    ->url(fn(Medicine $record) => route('medicines.edit', ['medicine' => $record]))
                    ->extraAttributes(['wire:navigate' => 'true']),
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
            ->recordUrl(
                fn(Medicine $record) => route('medicines.view', ['medicine' => $record])
            );
    }

    public function render()
    {
        return view('livewire.pages.purchase.purchase-list');
    }
}
