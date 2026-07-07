<?php

namespace App\Livewire\Pages\Sales;

use App\Models\Sale;
use Filament\Actions\Concerns\InteractsWithActions;
use Filament\Actions\Contracts\HasActions;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Table;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.app')]
class SaleList extends Component implements HasActions, HasForms, HasTable
{
    use \App\Livewire\Pages\Sales\Concerns\HasSaleTable;
    use InteractsWithActions;
    use InteractsWithForms;
    use InteractsWithTable;

    public function table(Table $table): Table
    {
        return $table
            ->query(
                Sale::query()
                    ->with(['customer', 'branch', 'user'])
                    ->when(
                        ! auth()->user()?->hasRole('Super Admin'),
                        fn ($q) => $q->where('branch_id', activeBranch()?->id)
                    )
                    ->latest()
            )
            ->columns($this->getSaleTableColumns())
            ->filters($this->getSaleTableFilters())
            ->actions($this->getSaleTableActions())
            ->bulkActions([
                //
            ]);
    }

    public function render()
    {
        return view('livewire.sales.sale-list');
    }
}
