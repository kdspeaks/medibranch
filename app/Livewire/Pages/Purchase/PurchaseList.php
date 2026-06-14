<?php

namespace App\Livewire\Pages\Purchase;

use App\Models\Purchase;
use Filament\Actions\Action;
use Filament\Actions\Concerns\InteractsWithActions;
use Filament\Actions\Contracts\HasActions;
use Filament\Actions\DeleteAction;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Table;
use Livewire\Component;

class PurchaseList extends Component implements HasForms, HasActions, HasTable
{
    use InteractsWithActions;
    use InteractsWithTable;
    use InteractsWithForms;

    public function table(Table $table): Table
    {
        return $table
            ->query($this->purchaseQuery())
            ->columns([
                TextColumn::make('reference')
                    ->label('Reference No')
                    ->state(fn (Purchase $record) => trim(($record->ref_code_prefix ?? '') . $record->ref_code_count))
                    ->searchable(['ref_code_prefix', 'ref_code_count'])
                    ->sortable(),
                TextColumn::make('invoice_number')
                    ->label('Invoice No')
                    ->placeholder('-')
                    ->searchable(),
                TextColumn::make('branch.name')
                    ->label('Branch')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('supplier.name')
                    ->label('Supplier')
                    ->placeholder('Walk-in')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('purchase_date')
                    ->label('Date')
                    ->date('d M, Y')
                    ->sortable(),
                TextColumn::make('status')
                    ->badge()
                    ->colors([
                        'gray' => 'draft',
                        'success' => 'received',
                        'danger' => 'cancelled',
                    ])
                    ->sortable(),
                TextColumn::make('total_amount')
                    ->label('Total')
                    ->money('INR')
                    ->sortable(),
            ])
            ->recordActions([
                Action::make('view')
                    ->icon('heroicon-m-eye')
                    ->url(fn (Purchase $record) => route('medicines.purchases.view', ['purchase' => $record]))
                    ->extraAttributes(['wire:navigate' => 'true']),
                Action::make('edit')
                    ->icon('heroicon-m-pencil-square')
                    ->visible(fn (Purchase $record) => $record->status !== 'received')
                    ->url(fn (Purchase $record) => route('medicines.purchases.edit', ['purchase' => $record]))
                    ->extraAttributes(['wire:navigate' => 'true']),
                DeleteAction::make()
                    ->visible(fn (Purchase $record) => $record->status !== 'received')
                    ->requiresConfirmation(),
            ])
            ->paginated([10, 20, 50, 100, 'all'])
            ->defaultPaginationPageOption(20)
            ->recordUrl(fn (Purchase $record) => route('medicines.purchases.view', ['purchase' => $record]));
    }

    public function render()
    {
        return view('livewire.pages.purchase.purchase-list');
    }

    private function purchaseQuery()
    {
        $query = Purchase::query()->with(['branch', 'supplier']);
        $branch = activeBranch();
        $user = auth()->user();

        if ($branch && $user && ! $user->hasRole('Super Admin')) {
            $query->forBranch($branch->id);
        }

        return $query;
    }
}
