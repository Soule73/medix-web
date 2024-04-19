<?php

namespace App\Filament\Resources;

use App\Enums\Doctor\DoctorStatusEnum;
use App\Enums\User\UserSexEnum;
use App\Filament\Resources\DoctorResource\Pages;
use App\Models\Doctor;
use Filament\Forms\Form;
use Filament\Infolists\Components;
use Filament\Infolists\Infolist;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class DoctorResource extends Resource
{

    protected static ?int $navigationSort = 1;

    protected static ?string $model = Doctor::class;

    protected static ?string $navigationIcon = 'heroicon-o-user-group';

    public static function getModelLabel(): string
    {
        return __('doctor/doctor.doctors-label');
    }

    public static function getNavigationLabel(): string
    {
        return __('doctor/doctor.doctors-label');
    }

    public static function getPluralLabel(): string
    {
        return __('doctor/doctor.doctors-plural-label');
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
            ->columns([
                Tables\Columns\TextColumn::make('user_fullname')->label(__('doctor/doctor.full-name')),
                Tables\Columns\TextColumn::make('user.email')->label(__('doctor/doctor.user-email')),
                Tables\Columns\TextColumn::make('user.sex')->label(__('doctor/doctor.user-sex'))
                    ->color('info')
                    ->formatStateUsing(function (UserSexEnum $state) {
                        return $state === UserSexEnum::MAN ? __('doctor/doctor.user-sex-man') : __('doctor/doctor.user-sex-woman');
                    }),
                Tables\Columns\TextColumn::make('status')
                    ->badge()
                    ->color(function (DoctorStatusEnum $state) {
                        return $state === DoctorStatusEnum::NOTVALIDATED ? 'danger' : 'success';
                    })
                    ->formatStateUsing(function (DoctorStatusEnum $state) {
                        return $state === DoctorStatusEnum::NOTVALIDATED ? __('doctor/doctor.doctor-status-notvalidated') :
                            __('doctor/doctor.doctor-status-validated');
                    }),

            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                // Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function infolist(Infolist $infolist): Infolist
    {
        return $infolist
            ->schema([

                Components\Section::make(__('doctor/doctor.infolist-section-info'))
                    ->schema([
                        Components\Split::make([
                            Components\Grid::make(2)
                                ->schema([
                                    Components\Group::make([
                                        Components\TextEntry::make('user_fullname')->label(__('doctor/doctor.full-name')),
                                        Components\TextEntry::make('user.email')->label(__('doctor/doctor.user-email')),
                                        Components\TextEntry::make('user.phone')->label(__('doctor/doctor.user-phone')),

                                    ]),
                                    Components\Group::make([
                                        Components\TextEntry::make('user.sex')
                                            ->label(__('doctor/doctor.user-sex'))
                                            ->color('info')
                                            ->formatStateUsing(function (UserSexEnum $state) {
                                                return $state === UserSexEnum::MAN ? __('doctor/doctor.user-sex-man') :
                                                    __('doctor/doctor.user-sex-woman');
                                            }),
                                        Components\TextEntry::make('created_at')->label(__('doctor/doctor.user-created-at'))
                                            ->badge()
                                            ->date(session()->get('local') !== 'en' ? 'd M Y' : null)
                                            ->color('success'),
                                        Components\TextEntry::make('status')
                                            ->label(__('doctor/doctor.doctor-status'))
                                            ->badge()
                                            ->color(function (DoctorStatusEnum $state) {
                                                return $state === DoctorStatusEnum::NOTVALIDATED ? 'danger' : 'success';
                                            })
                                            ->formatStateUsing(function (DoctorStatusEnum $state) {
                                                return $state === DoctorStatusEnum::NOTVALIDATED ? __('doctor/doctor.doctor-status-notvalidated') :
                                                    __('doctor/doctor.doctor-status-validated');
                                            }),

                                    ]),
                                ]),
                            Components\ImageEntry::make('user.avatar')
                                ->hiddenLabel()
                                ->disk('local')
                                ->circular()
                                ->grow(false),
                        ])->from('lg'),
                    ])->collapsible(),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            DoctorResource\RelationManagers\DocumentsForValidationsRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListDoctors::route('/'),
            'create' => Pages\CreateDoctor::route('/create'),
            'view' => Pages\ViewDoctor::route('/{record}'),
            // 'edit' => Pages\EditDoctor::route('/{record}/edit'),
        ];
    }

    public static function canCreate(): bool
    {
        return false;
    }
}
