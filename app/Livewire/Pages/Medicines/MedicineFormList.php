<?php

namespace App\Livewire\Pages\Medicines;

use Livewire\Component;
use Filament\Tables\Table;
use App\Models\MedicineForm;
use App\Models\MedicineUnit;
use Filament\Actions\Action;
use Filament\Actions\CreateAction;
use Filament\Actions\EditAction;
use Filament\Actions\DeleteAction;
use Filament\Schemas\Components\Group;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\ToggleButtons;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ToggleColumn;
use Filament\Forms\Contracts\HasForms;
use Filament\Tables\Contracts\HasTable;
use Filament\Actions\Contracts\HasActions;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Actions\Concerns\InteractsWithActions;

class MedicineFormList extends Component implements HasForms, HasActions, HasTable
{
    use InteractsWithActions;
    use InteractsWithTable;
    use InteractsWithForms;

    public function createAction(): Action
    {
        return CreateAction::make('create')
            ->model(MedicineForm::class)
            ->label('Create Form & Units')
            ->modalHeading('Create New Form')
            ->schema($this->getFormSchema());
    }

    public function table(Table $table): Table
    {
        return $table
            ->query(MedicineForm::query())
            ->columns([
                TextColumn::make('name')
                    ->label('Form Name')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('units.name')
                    ->label('Associated Units')
                    ->badge()
                    ->separator(',')
                    ->searchable(),
                ToggleColumn::make('is_active')
                    ->label('Active')
                    ->onIcon('heroicon-m-check-circle')
                    ->offIcon('heroicon-m-x-circle')
                    ->toggleable()
                    ->default(true)
                    ->sortable()
            ])
            ->recordActions([
                EditAction::make()
                    ->modalHeading('Edit Form & Units')
                    ->schema($this->getFormSchema()),
                DeleteAction::make()
                    ->requiresConfirmation()
            ])
            ->paginated([10, 20, 50, 100, 'all'])
            ->defaultPaginationPageOption(20)
            ->striped();
    }

    protected function getFormSchema(): array
    {
        return [
            Group::make()
                ->schema([
                    TextInput::make('name')
                        ->label('Form Name')
                        ->required()
                        ->unique(ignoreRecord: true)
                        ->maxLength(255),
                    
                    ToggleButtons::make('is_active')
                        ->label('Active')
                        ->boolean()
                        ->inline()
                        ->default(true),

                    Select::make('units')
                        ->label('Units')
                        ->multiple()
                        ->relationship('units', 'name')
                        ->preload()
                        ->createOptionForm([
                            TextInput::make('name')
                                ->label('Unit Name (e.g. ml, g, strip)')
                                ->required()
                                ->unique(table: 'medicine_units', column: 'name')
                                ->maxLength(255),
                            TextInput::make('short_code')
                                ->label('Short Code (SKU suffix)')
                                ->maxLength(255),
                            ToggleButtons::make('is_active')
                                ->label('Active')
                                ->boolean()
                                ->inline()
                                ->default(true),
                        ])
                        ->columnSpanFull()
                ])
                ->columns(2)
                ->columnSpanFull()
        ];
    }

    public function render()
    {
        return view('livewire.pages.medicines.medicine-form-list');
    }
}
