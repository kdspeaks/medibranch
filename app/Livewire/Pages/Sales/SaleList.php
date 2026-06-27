<?php

namespace App\Livewire\Pages\Sales;

use App\Models\Sale;
use Livewire\Component;
use Filament\Tables\Table;
use Filament\Forms\Contracts\HasForms;
use Filament\Tables\Contracts\HasTable;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Actions\Contracts\HasActions;
use Filament\Actions\Concerns\InteractsWithActions;
use Livewire\Attributes\Layout;

#[Layout('layouts.app')]
class SaleList extends Component implements HasForms, HasTable, HasActions
{
    use InteractsWithTable;
    use InteractsWithForms;
    use InteractsWithActions;

    public function table(Table $table): Table
    {
        return $table
            ->query(Sale::query()->with(['customer', 'branch', 'user'])->latest())
            ->columns([
                TextColumn::make('invoice_number')->label(__('messages.invoice_no'))->searchable()->sortable(),
                TextColumn::make('sale_date')->label(__('messages.date'))->dateTime()->sortable(),
                TextColumn::make('branch.name')->label(__('messages.branch'))->sortable()->toggleable(),
                TextColumn::make('customer.name')->label(__('messages.customers'))->searchable()->sortable()
                    ->formatStateUsing(fn ($state) => $state ?? __('messages.walk_in')),
                TextColumn::make('total_amount')->label(__('messages.total_amount'))->money('BDT', divideBy: 0)->sortable(),
                TextColumn::make('payment_method')->label(__('messages.payment_method'))->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'cash' => 'success',
                        'card' => 'info',
                        'upi' => 'warning',
                        default => 'gray',
                    })->formatStateUsing(fn ($state) => strtoupper($state)),
                TextColumn::make('payment_status')->label(__('messages.payment_status'))->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'paid' => 'success',
                        'partial' => 'warning',
                        'unpaid' => 'danger',
                        default => 'gray',
                    }),
            ])
            ->filters([
                SelectFilter::make('branch_id')
                    ->label(__('messages.branch'))
                    ->relationship('branch', 'name'),
                SelectFilter::make('payment_method')
                    ->label(__('messages.payment_method'))
                    ->options([
                        'cash' => __('messages.cash'),
                        'card' => __('messages.card'),
                        'upi' => __('messages.upi'),
                    ]),
            ])
            ->actions([
                //
            ])
            ->bulkActions([
                //
            ]);
    }

    public function render()
    {
        return view('livewire.sales.sale-list');
    }
}
