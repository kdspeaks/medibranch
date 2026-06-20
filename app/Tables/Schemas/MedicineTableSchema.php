<?php

namespace App\Tables\Schemas;

use App\Models\Medicine;
use Filament\Tables\Table;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ViewColumn;
use Filament\Tables\Columns\ToggleColumn;
use Filament\Notifications\Notification;
use Illuminate\Support\Facades\Auth;

class MedicineTableSchema
{
    public static function table(Table $table, $queryBuilder = null): Table
    {
        return $table
            ->query($queryBuilder ?? Medicine::query()->with('tax'))
            ->columns([
                ViewColumn::make('name')
                    ->view('components.datatable.medicine_name')
                    ->searchable(['name', 'sku'])
                    ->sortable(),

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
                    ->visible(Auth::user()?->can('manage-medicines'))
                    ->afterStateUpdated(function ($record, $state) {
                        Notification::make()
                            ->title('Medicine Updated')
                            ->body('Medicine has been successfully updated.')
                            ->success()
                            ->send();
                    }),
            ])
            ->recordActions([
                \Filament\Actions\Action::make('edit')
                    ->icon('heroicon-m-pencil-square')
                    ->url(fn(Medicine $record) => route('medicines.edit', ['medicine' => $record]))
                    ->extraAttributes(['wire:navigate' => true]),
                \Filament\Actions\DeleteAction::make()
                    ->visible(fn($record) => $record->name !== 'Super Admin')
                    ->requiresConfirmation()
            ])
            ->paginated([10, 20, 50, 100, 'all'])
            ->defaultPaginationPageOption(20)
            ->recordUrl(
                fn(Medicine $record) => route('medicines.view', ['medicine' => $record])
            )
            ->striped();
    }
}
