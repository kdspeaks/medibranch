<?php

namespace App\Livewire\Pages\Purchase;

use App\Models\Purchase;
use Filament\Actions\Concerns\InteractsWithActions;
use Filament\Actions\Contracts\HasActions;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Table;
use Livewire\Component;
use App\Tables\Schemas\PurchaseTableSchema;

class PurchaseList extends Component implements HasForms, HasActions, HasTable
{
    use InteractsWithActions;
    use InteractsWithTable;
    use InteractsWithForms;

    public function table(Table $table): Table
    {
        return PurchaseTableSchema::table($table, $this->purchaseQuery());
    }

    public function render()
    {
        return view('livewire.pages.purchase.purchase-list');
    }

    private function purchaseQuery()
    {
        $query = Purchase::query()->with(['branch', 'supplier']);
        $user = auth()->user();

        if ($user) {
            $query->forUserBranches($user);
        }

        return $query;
    }
}
