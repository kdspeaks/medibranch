<?php

namespace App\Livewire\Pages\Customers;

use App\Models\Customer;
use Livewire\Component;
use Filament\Tables\Table;
use Filament\Forms\Contracts\HasForms;
use Filament\Tables\Contracts\HasTable;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Columns\TextColumn;
use Filament\Actions\EditAction;
use Filament\Actions\Action;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Actions\DeleteAction;
use Filament\Actions\Contracts\HasActions;
use Filament\Actions\Concerns\InteractsWithActions;
use Livewire\Attributes\Layout;

#[Layout('layouts.app')]
class CustomerList extends Component implements HasForms, HasTable, HasActions
{
    use InteractsWithTable;
    use InteractsWithForms;
    use InteractsWithActions;

    public function createAction(): \Filament\Actions\Action
    {
        return \Filament\Actions\CreateAction::make('create')
            ->modalHeading(__('messages.create_customer'))
            ->model(Customer::class)
            ->label(__('messages.create_customer'))
            ->form([
                TextInput::make('name')->label(__('messages.customer_name'))->required()->maxLength(255),
                TextInput::make('phone')->label(__('messages.phone_number'))->required()->maxLength(20)->unique(Customer::class, ignoreRecord: true),
                TextInput::make('email')->label(__('messages.email'))->email()->maxLength(255),
                Textarea::make('address')->label(__('messages.address')),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->query(Customer::query())
            ->columns([
                TextColumn::make('name')->label(__('messages.customer_name'))->searchable()->sortable(),
                TextColumn::make('phone')->label(__('messages.phone_number'))->searchable(),
                TextColumn::make('email')->label(__('messages.email'))->searchable(),
                TextColumn::make('created_at')->dateTime()->sortable()->toggleable(isToggledHiddenByDefault: true),
            ])
            ->recordActions([
                Action::make('view')
                    ->label(__('messages.view') ?? 'View')
                    ->icon('heroicon-o-eye')
                    ->url(fn (Customer $record): string => route('customers.view', $record)),
                EditAction::make()
                    ->modalHeading(__('messages.edit_customer') ?? 'Edit Customer')
                    ->form([
                        TextInput::make('name')->label(__('messages.customer_name'))->required()->maxLength(255),
                        TextInput::make('phone')->label(__('messages.phone_number'))->required()->maxLength(20)->unique(ignoreRecord: true),
                        TextInput::make('email')->label(__('messages.email'))->email()->maxLength(255),
                        Textarea::make('address')->label(__('messages.address')),
                    ]),
                DeleteAction::make()->requiresConfirmation(),
            ])
            ->paginated([10, 20, 50, 100, 'all'])
            ->defaultPaginationPageOption(20)
            ->striped();
    }

    public function render()
    {
        return view('livewire.customers.customer-list');
    }
}
