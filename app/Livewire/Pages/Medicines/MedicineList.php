<?php

namespace App\Livewire\Pages\Medicines;

use Livewire\Component;
use Filament\Tables\Table;
use Filament\Forms\Contracts\HasForms;
use Filament\Tables\Contracts\HasTable;
use Filament\Actions\Contracts\HasActions;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Actions\Concerns\InteractsWithActions;
use App\Tables\Schemas\MedicineTableSchema;

class MedicineList extends Component implements HasForms, HasActions, HasTable
{
    use InteractsWithActions;
    use InteractsWithTable;
    use InteractsWithForms;

    public function table(Table $table): Table
    {
        return MedicineTableSchema::table($table);
    }

    public function render()
    {
        return view('livewire.pages.medicines.medicine-list');
    }
}
