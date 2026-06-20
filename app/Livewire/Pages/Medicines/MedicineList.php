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
use Filament\Actions\Action;
use Filament\Forms\Components\FileUpload;
use Maatwebsite\Excel\Facades\Excel;
use App\Imports\MedicinesImport;
use Filament\Notifications\Notification;

class MedicineList extends Component implements HasForms, HasActions, HasTable
{
    use InteractsWithActions;
    use InteractsWithTable;
    use InteractsWithForms;

    public function table(Table $table): Table
    {
        return MedicineTableSchema::table($table);
    }

    public function importAction(): Action
    {
        return Action::make('import')
            ->label('Import Medicines')
            ->icon('heroicon-o-arrow-down-tray')
            ->color('success')
            ->form([
                FileUpload::make('file')
                    ->label('Excel File (.xlsx)')
                    ->acceptedFileTypes(['application/vnd.openxmlformats-officedocument.spreadsheetml.sheet', 'application/vnd.ms-excel', 'text/csv'])
                    ->required()
            ])
            ->action(function (array $data) {
                // $data['file'] contains the path on the 'public' disk
                $filePath = storage_path('app/public/' . $data['file']);
                
                $import = new MedicinesImport();
                Excel::import($import, $filePath);

                Notification::make()
                    ->title('Import Successful')
                    ->body("Imported {$import->importedCount} new medicines and updated {$import->updatedCount} existing medicines.")
                    ->success()
                    ->send();
            });
    }

    public function render()
    {
        return view('livewire.pages.medicines.medicine-list');
    }
}
