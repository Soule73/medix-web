<?php

namespace App\Filament\Resources;

use App\Filament\Resources\SpecialityResource\Pages;
use App\Models\Speciality;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class SpecialityResource extends Resource
{
    protected static ?int $navigationSort = 2;

    protected static ?string $model = Speciality::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function getModelLabel(): string
    {
        return __('doctor/relation/speciality.modelLabel');
    }

    public static function getNavigationLabel(): string
    {
        return __('doctor/relation/speciality.navigation-label');
    }

    public static function getPluralLabel(): string
    {
        return __('doctor/relation/speciality.plural-label');
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make(__('doctor/relation/speciality.attention'))
                    ->description(__('doctor/relation/speciality.please-add-translation'))
                    ->aside()
                    ->schema([
                        Forms\Components\TextInput::make('name')->label(__('doctor/doctor.user-name'))
                            ->unique()
                            ->required()
                            ->validationMessages([
                                'unique' => __('doctor/relation/speciality.unique-speciality'),
                            ]),
                        Forms\Components\Textarea::make('description')->label(__('doctor/relation/speciality.description'))
                            ->columnSpanFull()
                            ->autosize(),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->formatStateUsing(fn (string $state): string => __("doctor/relation/speciality.$state.name"))
                    ->label(__('doctor/doctor.full-name'))
                    ->sortable()
                    ->searchable(),
                Tables\Columns\TextColumn::make('description')
                    ->formatStateUsing(fn (string $state): string => __('doctor/relation/speciality.'.Speciality::where('description', $state)->first()->name.'.description'))
                    ->label(__('doctor/relation/speciality.description')),
            ])->defaultSort('name', 'asc')
            ->filters([
                //
            ])
            ->headerActions(
                [
                    // Tables\Actions\CreateAction::make()->label(__('actions.add'))
                ]
            )
            ->actions([
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListSpecialities::route('/'),
            // 'create' => Pages\CreateSpeciality::route('/create'),
            // 'edit' => Pages\EditSpeciality::route('/{record}/edit'),
        ];
    }
}
