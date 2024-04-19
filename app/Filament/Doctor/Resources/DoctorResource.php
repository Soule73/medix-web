<?php

namespace App\Filament\Doctor\Resources;

use App\Enums\Doctor\DoctorStatusEnum;
use App\Enums\LangEnum;
use App\Enums\User\UserSexEnum;
use App\Models\Doctor;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Infolists\Components;
use Filament\Infolists\Components\TextEntry\TextEntrySize;
use Filament\Infolists\Infolist;
use Filament\Resources\Resource;
use Illuminate\Database\Eloquent\Builder;

class DoctorResource extends Resource
{
    protected static ?int $navigationSort = 1;

    protected static ?string $model = Doctor::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function getNavigationGroup(): ?string
    {
        return __('doctor/doctor.navigation-group');
    }

    public static function getModelLabel(): string
    {
        return __('doctor/doctor.modelLabel');
    }

    public static function getPluralLabel(): string
    {
        return __('doctor/doctor.modelLabel');
    }

    public static function form(Form $form): Form
    {
        return $form

            ->schema([
                Forms\Components\Grid::make(1)
                    ->schema([
                        Forms\Components\Grid::make(1)
                            ->relationship('user')
                            ->schema([
                                Forms\Components\FileUpload::make('avatar')
                                    ->label(__('doctor/profile.edit-profile-form-avatar'))
                                    ->avatar()
                                    ->disk('local')
                                    ->directory('public/users/images')
                                    ->visibility('public'),
                                Forms\Components\Select::make('default_lang')
                                    ->helperText('Langue utiliser pour vous envoyer des notifications')
                                    ->options(
                                        [
                                            LangEnum::FR->value => 'Français',
                                            LangEnum::AR->value => 'العربية',
                                            LangEnum::EN->value => 'English',
                                        ]
                                    )
                                    ->label(__('doctor/doctor.default-lang')),
                            ]),
                        Forms\Components\TextInput::make('visit_price')
                            ->label(__('doctor/doctor.ressource-visit-price'))
                            ->numeric()
                            ->prefix('MRU'),
                        Forms\Components\TextInput::make('year_experience')
                            ->numeric()
                            ->label(__('doctor/doctor.year-experience'))->suffix('Années'),

                        Forms\Components\Textarea::make('bio')->autosize()
                            ->label(__('doctor/doctor.ressource-bio'))->maxLength(1000),
                    ]),
            ]);
    }

    // public static function table(Table $table): Table
    // {
    //     return $table
    //         ->columns([
    //             Tables\Columns\TextColumn::make('user_fullname')->label(__('doctor/doctor.full-name')),
    //             Tables\Columns\TextColumn::make('user.email')->label(__('doctor/doctor.user-email')),
    //             Tables\Columns\TextColumn::make('user.sex')->label(__('doctor/doctor.user-sex'))
    //                 ->color('info')
    //                 ->formatStateUsing(function (UserSexEnum $state) {
    //                     return   $state === UserSexEnum::MAN ? __('doctor/doctor.user-sex-man') : __('doctor/doctor.user-sex-woman');
    //                 }),
    //             Tables\Columns\TextColumn::make('status')
    //                 ->badge()
    //                 ->color(function (DoctorStatusEnum $state) {
    //                     return   $state === DoctorStatusEnum::NOTVALIDATED ? 'danger' : 'success';
    //                 })
    //                 ->formatStateUsing(function (DoctorStatusEnum $state) {
    //                     return   $state === DoctorStatusEnum::NOTVALIDATED ? __('doctor/doctor.doctor-status-notvalidated') :
    //                         __('doctor/doctor.doctor-status-validated');
    //                 }),

    //         ])
    //         ->paginated(false)
    //         ->filters([
    //             //
    //         ])
    //         ->actions([
    //             Tables\Actions\ViewAction::make(),
    //             Tables\Actions\EditAction::make(),
    //         ])
    //         ->bulkActions([
    //             Tables\Actions\BulkActionGroup::make([
    //                 Tables\Actions\DeleteBulkAction::make(),
    //             ]),
    //         ]);
    // }

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
                                        Components\TextEntry::make('user.default_lang')
                                            ->formatStateUsing(function (LangEnum $state) {
                                                $value = '';

                                                switch ($state) {
                                                    case LangEnum::FR: {
                                                            $value = 'Français';
                                                            break;
                                                        }
                                                    case LangEnum::EN: {
                                                            $value = 'English';
                                                            break;
                                                        }
                                                    default:
                                                        $value = 'العربية';
                                                        break;
                                                }
                                                return $value;
                                            })
                                            ->label(__('doctor/doctor.default-lang')),


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
                                        Components\TextEntry::make('year_experience')->label(__('doctor/doctor.year-experience'))
                                            // ->badge()
                                            ->iconColor("info")
                                            ->size(TextEntrySize::Large)
                                            ->icon('heroicon-o-academic-cap')
                                            ->iconPosition('after')
                                            ->color('info'),
                                        Components\TextEntry::make('status')
                                            ->label(__('doctor/doctor.doctor-status'))
                                            ->badge()
                                            ->icon(fn (DoctorStatusEnum $state) => $state == DoctorStatusEnum::VALIDATED ? 'heroicon-s-check-badge' : null)
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
            DoctorResource\RelationManagers\DoctorSpecialityRelationManager::class,
            DoctorResource\RelationManagers\QualificationsRelationManager::class,
            DoctorResource\RelationManagers\DocumentsForValidationsRelationManager::class,
            DoctorResource\RelationManagers\WorkPlacesRelationManager::class,
            DoctorResource\RelationManagers\WorkingHoursRelationManager::class,
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->where('user_id', auth()->id());
    }

    public static function getPages(): array
    {
        return [
            // 'index' => Pages\ListDoctors::route('/'),
            // 'view' => Pages\ViewDoctor::route('/{record}'),
            // 'create' => Pages\CreateDoctor::route('/create'),
            'index' => DoctorResource\Pages\ViewDoctor::route('/'),
            // 'edit' => DoctorResource\Pages\EditDoctor::route('/{record}/edit'),
        ];
    }

    public static function canCreate(): bool
    {
        return false;
    }
}
