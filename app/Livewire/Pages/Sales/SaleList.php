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
    use \App\Livewire\Pages\Sales\Concerns\HasSaleTable;

    public function table(Table $table): Table
    {
        return $table
            ->query(Sale::query()->with(['customer', 'branch', 'user'])->latest())
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
