<?php

namespace App\Filament\Doctor\Resources\DoctorResource\RelationManagers;

use Filament\Forms;
use Filament\Tables;
use Filament\Forms\Form;
use Filament\Tables\Table;
use Illuminate\Support\Str;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Support\Enums\MaxWidth;

class WorkPlacesRelationManager extends RelationManager
{
    protected static string $relationship = 'work_places';

    // protected static ?string $modelLabel = "Lieu de travail";

    // protected static ?string $label = "Lieux des travails";
    // protected static ?string $title = "Lieux des travails";

    public static function getTitle($ownerRecord, string $pageClass): string
    {
        return __('doctor/relation/work-place.modelLabel');
    }

    protected static function getModelLabel(): ?string
    {
        return __('doctor/relation/work-place.modelLabel');
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Grid::make(1)
                    ->schema([
                        Forms\Components\TextInput::make('name')->label(__('doctor/doctor.full-name'))
                            ->required()
                            ->validationMessages([
                                'required' => __('doctor/relation/work-place.form-name-required'),
                            ])
                            ->maxLength(255),

                        Forms\Components\TextInput::make('address')->label(__('doctor/relation/work-place.adress'))
                            ->required()
                            ->validationMessages([
                                'required' => __('doctor/relation/work-place.form-adress-required'),
                            ])
                            ->maxLength(255),
                        Forms\Components\Select::make('city_id')->label(__('doctor/relation/work-place.city'))
                            ->relationship('city', 'name')
                            ->preload()
                            ->searchable()
                            ->required()
                            ->validationMessages([
                                'required' => __('doctor/relation/work-place.form-city-required'),
                            ]),
                        Forms\Components\Section::make(__('doctor/relation/work-place.form-section-title'))
                            ->description(__('doctor/relation/work-place.form-section-description'))
                            ->schema([
                                Forms\Components\Split::make(
                                    [Forms\Components\Grid::make(1)
                                        ->schema([
                                            Forms\Components\TextInput::make(__('doctor/relation/work-place.longitude'))->numeric(),
                                            Forms\Components\TextInput::make(__('doctor/relation/work-place.latitude'))
                                                ->numeric(),
                                        ])]

                                )
                            ])
                    ])
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->emptyStateIcon('heroicon-o-building-office')
            ->paginated(true)
            ->emptyStateHeading(__('doctor/relation/work-place.table-emptyStateHeading'))
            ->emptyStateDescription(__('doctor/relation/work-place.table-emptyStateDescription'))
            ->emptyStateActions([
                Tables\Actions\CreateAction::make()
                    ->modalHeading(__('doctor/doctor.add-a-specialty', ['add' => Str::lower(self::getModelLabel())]))

                    ->mutateFormDataUsing(function ($data) {
                        $data['doctor_id'] = auth()->user()->doctor->id;

                        return $data;
                    })
                    ->label(__('actions.add'))->icon('heroicon-o-plus'),
            ])
            ->recordTitleAttribute('name')
            ->columns([
                Tables\Columns\TextColumn::make('name')->label(__('doctor/doctor.full-name'))->searchable()->sortable(),
                Tables\Columns\TextColumn::make('address')->label(__('doctor/relation/work-place.adress'))->searchable()->sortable(),
                Tables\Columns\TextColumn::make('city.name')->label(__('doctor/relation/work-place.city'))->searchable()->sortable(),
                Tables\Columns\TextColumn::make('latitude')->label(__('doctor/relation/work-place.latitude')),
                Tables\Columns\TextColumn::make('longitude')->label(__('doctor/relation/work-place.longitude')),
            ])
            ->filters([
                //
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make()->label(__('actions.add'))->icon('heroicon-o-plus')
                    ->modalSubmitActionLabel(__('actions.add'))
                    ->modalWidth(MaxWidth::Large)
                    ->modalHeading(__('doctor/doctor.add-a-working-place', ['add' => Str::lower(self::getModelLabel())]))

                    ->mutateFormDataUsing(function ($data) {
                        $data['doctor_id'] = auth()->user()->doctor->id;

                        return $data;
                    }),
            ])
            ->actions([
                Tables\Actions\EditAction::make()
                    ->modalWidth(MaxWidth::Large),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public function isReadOnly(): bool
    {
        return false;
    }
}
