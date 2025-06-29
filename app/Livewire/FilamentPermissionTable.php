<?php

namespace App\Livewire;

use Filament\Tables\Actions\CreateAction;
use Livewire\Component;
use Filament\Tables\Table;
use Illuminate\Contracts\View\View;
use Filament\Forms\Contracts\HasForms;
use Filament\Tables\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Contracts\HasTable;
use Filament\Forms\Components\TextInput;
use Spatie\Permission\Models\Permission;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Tables\Concerns\InteractsWithTable;
use GuzzleHttp\Promise\Create;

class FilamentPermissionTable extends Component implements HasForms, HasTable
{
    use InteractsWithTable;
    use InteractsWithForms;
    
    public function table(Table $table): Table
    {
        return $table
            ->query(Permission::query())
            
            ->columns([
                TextColumn::make('name')
                ->searchable(),
            ])
            ->filters([
                // ...
            ])
            ->actions([
                EditAction::make()
                ->form([
                    TextInput::make('name')
                        ->required()
                        ->maxLength(255),
                    // ...
                ]),
                CreateAction::make()
                ->form([
                    TextInput::make('name')
                    ->model(Permission::class)
                        ->required()
                        ->maxLength(255),
                    // ...
                ]),
            ])
            ->bulkActions([
                // ...
            ])
            ->headerActions([
                
            ])
            ->paginated([10, 20, 50, 100, 'all'])
             ->defaultPaginationPageOption(20);;
    }

    public function render()
    {
        return view('livewire.filament-permission-table');
    }
}
