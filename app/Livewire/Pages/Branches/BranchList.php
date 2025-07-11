<?php

namespace App\Livewire\Pages\Branches;

use App\Models\Branch;
use Livewire\Component;
use Filament\Tables\Table;
use Filament\Actions\Action;
use Livewire\Attributes\Title;
use Filament\Actions\CreateAction;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Roles;
use Filament\Forms\Components\Group;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Components\Textarea;
use Filament\Tables\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Contracts\HasTable;
use Filament\Forms\Components\TextInput;
use Spatie\Permission\Models\Permission;
use Filament\Tables\Actions\DeleteAction;
use Filament\Actions\Contracts\HasActions;
use Filament\Forms\Components\CheckboxList;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Actions\Concerns\InteractsWithActions;
use Filament\Forms\Components\Radio;
use Filament\Forms\Components\Section;
use Filament\Tables\Actions\CreateAction as ActionsCreateAction;
use Filament\Tables\Columns\IconColumn;

class BranchList extends Component  implements HasForms, HasActions, HasTable
{
    use InteractsWithActions;
    use InteractsWithTable;
    use InteractsWithForms;

    private $lastBranch;

    

    public function createAction(): Action
    {
        //Get the last branch code and increment it for the new branch
        $id = 4;
        return CreateAction::make('create')
            ->model(Branch::class)
            ->label('Create Branch')
            ->modalHeading('Create New Branch')
            ->form([
                Group::make([
                    Section::make()
                        ->columns([
                            'sm' => 2
                        ])
                        ->schema([
                            TextInput::make('name')
                                ->unique(ignoreRecord: true)
                                ->label('Branch Name')
                                ->required()
                                ->maxLength(255),

                            TextInput::make('code')
                                ->unique(ignoreRecord: true)
                                ->label('Branch Code')
                                ->required()
                                ->default(fn() => "MEDB/{$id}" ?? "MEDB/1")
                                ->maxLength(255),
                        ]),
                    Section::make()
                        ->columns([
                            'sm' => 2
                        ])
                        ->schema([
                            Textarea::make('address')
                                ->label('Address')
                                ->maxLength(255)
                                ->columnSpan(2),
                            TextInput::make('phone')
                                ->label('Phone')
                                ->maxLength(20),
                            TextInput::make('email')
                                ->label('Email')
                                ->email()
                                ->maxLength(255),
                            Radio::make('is_active')
                                ->label('Is Active')
                                ->boolean()
                                ->default(true)
                                ->inline()
                        ]),

                ])
            ])

        ;
    }

    public function table(Table $table): Table
    {
        return $table
            ->query(Branch::query())

            ->columns([

                TextColumn::make('code')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('name')
                    ->searchable()
                    ->sortable(),
                IconColumn::make('is_active')
                    ->boolean(),
                TextColumn::make('phone')
                    ->searchable()
            ])
            ->filters([
                // ...
            ])
            ->actions([
                EditAction::make()
                    ->modalHeading('Edit Permission')
                    ->visible(fn($record) => $record->name !== 'Super Admin')
                    ->form([
                        Group::make([
                            Section::make()
                                ->columns([
                                    'sm' => 2
                                ])
                                ->schema([
                                    TextInput::make('name')
                                        ->unique(ignoreRecord: true)
                                        ->label('Branch Name')
                                        ->required()
                                        ->maxLength(255),

                                    TextInput::make('code')
                                        ->unique(ignoreRecord: true)
                                        ->label('Branch Code')
                                        ->required()
                                        // ->default(fn() => 'MEDB/' . $this->lastBranch?->id + 1 ?? 'MEDB/1')
                                        ->maxLength(255),
                                ]),
                            Section::make()
                                ->columns([
                                    'sm' => 2
                                ])
                                ->schema([
                                    Textarea::make('address')
                                        ->label('Address')
                                        ->maxLength(255)
                                        ->columnSpan(2),
                                    TextInput::make('phone')
                                        ->label('Phone')
                                        ->maxLength(20),
                                    TextInput::make('email')
                                        ->label('Email')
                                        ->email()
                                        ->maxLength(255),
                                    Radio::make('is_active')
                                        ->label('Is Active')
                                        ->boolean()
                                        ->default(true)
                                        ->inline()
                                ]),

                        ])
                    ]),
                DeleteAction::make()
                    ->visible(fn($record) => $record->name !== 'Super Admin')
                    ->requiresConfirmation()
            ])
            ->bulkActions([
                // ...
            ])
            ->headerActions([])
            ->paginated([10, 20, 50, 100, 'all'])
            ->defaultPaginationPageOption(20);;
    }

    public function render()
    {
        return view('livewire.pages.branches.branch-list');
    }

    public function mount()
    {
        $this->lastBranch = Branch::orderByDesc('id')->first();
    }
}
