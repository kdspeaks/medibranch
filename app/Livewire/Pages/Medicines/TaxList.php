<?php

namespace App\Livewire\Pages\Medicines;

use App\Models\Tax;
use Filament\Actions\Action;
use Filament\Actions\Concerns\InteractsWithActions;
use Filament\Actions\Contracts\HasActions;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\ToggleButtons;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Schemas\Components\Group;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ToggleColumn;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Table;
use Livewire\Component;

class TaxList extends Component implements HasActions, HasForms, HasTable
{
    use InteractsWithActions;
    use InteractsWithForms;
    use InteractsWithTable;

    public function render()
    {
        return view('livewire.pages.medicines.tax-list');
    }

    protected function getFormSchema(): array
    {
        return [
            Group::make()
                ->schema([
                    TextInput::make('name')
                        ->label(__('messages.name'))
                        ->required()
                        ->maxLength(255),

                    TextInput::make('rate')
                        ->label(__('messages.rate'))
                        ->required()
                        ->maxLength(255),

                    ToggleButtons::make('is_active')
                        ->label(__('messages.is_active'))
                        ->boolean()
                        ->grouped()
                        ->default(true),
                ])
                ->columns(3)
                ->columnSpanFull(),
        ];
    }

    public function createAction(): Action
    {
        return CreateAction::make('create')
            ->modalHeading(__('messages.create_new_tax'))
            ->model(Tax::class)
            ->label(__('messages.create_tax'))
            ->schema($this->getFormSchema());
    }

    public function table(Table $table): Table
    {
        return $table
            ->query(Tax::query())

            ->columns([
                TextColumn::make('name')
                    ->label(__('messages.name'))
                    ->searchable()
                    ->sortable(),
                TextColumn::make('rate')
                    ->label(__('messages.rate'))
                    ->searchable()
                    ->sortable(),
                ToggleColumn::make('is_active')
                    ->label(__('messages.is_active'))
                    ->onIcon('heroicon-m-check-circle')
                    ->offIcon('heroicon-m-x-circle')
                    ->toggleable()
                    ->sortable(),
            ])
            ->filters([
                // ...
            ])
            ->recordActions([
                EditAction::make()
                    ->modalHeading(__('messages.edit_tax'))
                    ->schema($this->getFormSchema()),
                DeleteAction::make()
                    ->requiresConfirmation(),
            ])
            ->toolbarActions([
                // ...
            ])
            ->headerActions([])
            ->paginated([10, 20, 50, 100, 'all'])
            ->defaultPaginationPageOption(20)
            ->striped();
    }
}
