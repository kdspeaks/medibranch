<?php

namespace App\Livewire\Pages\Supplier;

use App\Models\Supplier;
use Filament\Actions\Action;
use Filament\Actions\Concerns\InteractsWithActions;
use Filament\Actions\Contracts\HasActions;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Schemas\Components\Group;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Table;
use Livewire\Component;

class SupplierList extends Component implements HasActions, HasForms, HasTable
{
    use InteractsWithActions;
    use InteractsWithForms;
    use InteractsWithTable;

    protected function getFormSchema(): array
    {
        return [
            Group::make()
                ->columns(2)
                ->schema([
                    TextInput::make('name')
                        ->label(__('messages.supplier_name'))
                        ->required()
                        ->maxLength(255)
                        ->placeholder(__('messages.enter_supplier_name')),

                    TextInput::make('contact_person')
                        ->label(__('messages.contact_person'))
                        ->maxLength(255)
                        ->placeholder(__('messages.enter_contact_person')),

                    TextInput::make('email')
                        ->label(__('messages.email'))
                        ->email()
                        ->maxLength(255)
                        ->placeholder(__('messages.enter_email_address')),

                    TextInput::make('phone')
                        ->label(__('messages.phone'))
                        ->tel()
                        ->maxLength(20)
                        ->placeholder(__('messages.enter_phone_number')),
                ]),

            Group::make()
                ->columns(2)
                ->schema([
                    TextInput::make('address')
                        ->label(__('messages.address'))
                        ->maxLength(255)
                        ->placeholder(__('messages.street_address')),

                    TextInput::make('city')
                        ->label(__('messages.city'))
                        ->maxLength(255),

                    TextInput::make('state')
                        ->label(__('messages.state'))
                        ->maxLength(255),

                    TextInput::make('country')
                        ->label(__('messages.country'))
                        ->maxLength(255),

                    TextInput::make('postal_code')
                        ->label(__('messages.postal_code'))
                        ->maxLength(20),
                ]),
        ];
    }

    public function createAction(): Action
    {
        return CreateAction::make('create')
            ->modalHeading(__('messages.create_new_supplier'))
            ->model(Supplier::class)
            ->label(__('messages.create_supplier'))
            ->schema($this->getFormSchema());
    }

    public function table(Table $table): Table
    {
        return $table
            ->query(Supplier::query())

            ->columns([
                TextColumn::make('name')
                    ->label(__('messages.supplier_name'))
                    ->searchable()
                    ->sortable(),
                TextColumn::make('contact_person')
                    ->label(__('messages.contact_person'))
                    ->searchable()
                    ->sortable(),
                TextColumn::make('email')
                    ->label(__('messages.email'))
                    ->searchable()
                    ->sortable(),
                TextColumn::make('phone')
                    ->label(__('messages.phone'))
                    ->searchable()
                    ->sortable(),

            ])
            ->filters([
                // ...
            ])
            ->recordActions([
                EditAction::make()
                    ->modalHeading(__('messages.edit_supplier'))
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

    public function render()
    {
        return view('livewire.pages.supplier.supplier-list');
    }
}
