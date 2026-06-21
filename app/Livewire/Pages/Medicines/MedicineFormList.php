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
            ->label(__('messages.create_form_and_units'))
            ->modalHeading(__('messages.create_new_form'))
            ->schema($this->getFormSchema());
    }

    public function table(Table $table): Table
    {
        return $table
            ->query(MedicineForm::query())
            ->columns([
                TextColumn::make('name')
                    ->label(__('messages.form_name'))
                    ->searchable()
                    ->sortable(),
                TextColumn::make('units.name')
                    ->label(__('messages.associated_units'))
                    ->badge()
                    ->separator(',')
                    ->searchable(),
                ToggleColumn::make('is_active')
                    ->label(__('messages.is_active'))
                    ->onIcon('heroicon-m-check-circle')
                    ->offIcon('heroicon-m-x-circle')
                    ->toggleable()
                    ->default(true)
                    ->sortable()
            ])
            ->recordActions([
                EditAction::make()
                    ->modalHeading(__('messages.edit_form_and_units'))
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
                        ->label(__('messages.form_name'))
                        ->required()
                        ->unique(ignoreRecord: true)
                        ->maxLength(255),
                    
                    ToggleButtons::make('is_active')
                        ->label(__('messages.is_active'))
                        ->boolean()
                        ->inline()
                        ->default(true),

                    Select::make('units')
                        ->label(__('messages.units'))
                        ->multiple()
                        ->relationship('units', 'name')
                        ->preload()
                        ->createOptionForm([
                            TextInput::make('name')
                                ->label(__('messages.unit_name_example'))
                                ->required()
                                ->unique(table: 'medicine_units', column: 'name')
                                ->maxLength(255),
                            TextInput::make('short_code')
                                ->label(__('messages.short_code_sku'))
                                ->maxLength(255),
                            ToggleButtons::make('is_active')
                                ->label(__('messages.is_active'))
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
