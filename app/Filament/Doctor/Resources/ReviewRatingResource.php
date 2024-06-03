<?php

namespace App\Filament\Doctor\Resources;

use App\Filament\Doctor\Resources\ReviewRatingResource\Pages;
use App\Models\ReviewRating;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Support\Enums\IconPosition;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;

class ReviewRatingResource extends Resource
{
    protected static ?string $model = ReviewRating::class;

    protected static ?int $navigationSort = 2;

    protected static ?string $navigationIcon = 'heroicon-o-star';

    public static function getNavigationLabel(): string
    {
        return __('doctor/patient.rating-and-reviews');
    }

    public static function getModelLabel(): string
    {
        return __('doctor/patient.rating-and-reviews');
    }

    public static function getPluralLabel(): string
    {
        return __('doctor/patient.rating-and-reviews');
    }

    public static function getNavigationGroup(): ?string
    {
        return __('doctor/doctor.navigation-group');
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                //
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->selectable(false)
            ->columns([
                Tables\Columns\ImageColumn::make('patient.user.avatar')
                    ->disk('local')
                    ->circular()
                    ->label('')
                    ->placeholder(__('doctor/patient.no-photo')),
                Tables\Columns\TextColumn::make('patient.user_fullname')
                    ->label(__('doctor/doctor.full-name')),
                Tables\Columns\TextColumn::make('star')
                    ->icon('heroicon-s-star')
                    ->label(__('doctor/patient.rating'))
                    ->iconColor('warning')
                    ->iconPosition(IconPosition::After),
                Tables\Columns\TextColumn::make('comment')
                    ->label(__('doctor/patient.review'))
                    ->wrap()
                    ->extraAttributes([
                        'class' => 'max-w-sm break-words',
                    ]),

            ])
            ->filters([
                //
            ])
            ->actions([
                // Tables\Actions\EditAction::make(),
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
            'index' => Pages\ListReviewRatings::route('/'),
            'create' => Pages\CreateReviewRating::route('/create'),
            'edit' => Pages\EditReviewRating::route('/{record}/edit'),
        ];
    }

    public static function canCreate(): bool
    {
        return false;
    }

    public static function canEdit(Model $record): bool
    {
        return false;
    }

    public static function canDelete(Model $record): bool
    {
        return false;
    }
}
