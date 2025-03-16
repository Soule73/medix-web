<?php

namespace App\Filament\Doctor\Resources;

use App\Enums\Appointment\AppointmentStatusEnum;
use App\Enums\User\UserSexEnum;
use App\Filament\Exports\PatientExporter;
use App\Models\Patient;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Infolists\Components;
use Filament\Infolists\Infolist;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class PatientResource extends Resource
{
    protected static ?int $navigationSort = 4;

    protected static ?string $model = Patient::class;

    protected static ?string $navigationIcon = 'heroicon-o-user-group';

    public static function getNavigationGroup(): ?string
    {
        return __('doctor/patient.navigation-group');
    }

    public static function getModelLabel(): string
    {
        return __('doctor/patient.model-label');
    }

    public static function getPluralLabel(): string
    {
        return __('doctor/patient.model-label-plural');
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Grid::make(1)
                    ->schema([
                        Forms\Components\TextInput::make('id_cnss')
                            ->label(__('doctor/patient.id-cnss')),
                        Forms\Components\DatePicker::make('birthday')
                            ->prefixIcon('heroicon-o-calendar-days')
                            ->native(false)
                            ->label(__('doctor/patient.user-birthday')),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->selectable(false)

            ->emptyStateIcon('heroicon-o-user-group')
            ->emptyStateHeading(__('doctor/patient.table-emptyStateHeading'))
            ->emptyStateDescription(__('doctor/patient.table-emptyStateDescription'))
            ->columns([
                Tables\Columns\ImageColumn::make('user.avatar')
                    ->disk('local')
                    ->circular()
                    ->label('')
                    ->placeholder(__('doctor/patient.no-photo')),
                Tables\Columns\TextColumn::make('user_fullname')->label(__('doctor/doctor.full-name'))
                    ->sortable()
                    ->searchable(),
                // Tables\Columns\TextColumn::make('user.email')
                //     ->label(__('doctor/doctor.user-email'))
                //     ->searchable()
                //     ->placeholder('Inconnue'),
                Tables\Columns\TextColumn::make('user.phone')
                    ->prefix('+222 ')
                    ->searchable()
                    ->label(__('doctor/doctor.user-phone'))->placeholder('Inconnue'),
                Tables\Columns\TextColumn::make('user.sex')
                    ->label(__('doctor/doctor.user-sex'))
                    ->color('info')
                    ->formatStateUsing(function (UserSexEnum $state) {
                        return $state === UserSexEnum::MAN ? __('doctor/doctor.user-sex-man') :
                            __('doctor/doctor.user-sex-woman');
                    }),
            ])
            ->filters([
                //
            ])
            ->headerActions([
                Tables\Actions\ExportAction::make()->label(__('doctor/patient.table-export-action'))
                    ->color('info')
                    ->outlined()
                    ->exporter(PatientExporter::class)
                    ->modifyQueryUsing(fn (Builder $query) => $query->whereHas('appointments', function ($query) {
                        $doctorId = auth()->user()->doctor->id;

                        return $query->where('doctor_id', $doctorId);
                    })),
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

                Components\Section::make(__('doctor/patient.infolist-section-patient'))
                    ->icon('heroicon-o-user-circle')
                    ->schema([
                        Components\Split::make([
                            Components\Grid::make(2)
                                ->schema([
                                    Components\Group::make([
                                        Components\TextEntry::make('user_fullname')->label(__('doctor/doctor.full-name')),
                                        Components\TextEntry::make('user.phone')
                                            ->prefix('+222 ')
                                            ->label(__('doctor/doctor.user-phone')),
                                        Components\TextEntry::make('id_cnss')
                                            ->copyable()
                                            ->formatStateUsing(fn ($state) => Str::upper($state))
                                            ->label(__('doctor/patient.id-cnss')),

                                    ]),
                                    Components\Group::make([
                                        Components\TextEntry::make('user.sex')
                                            ->label(__('doctor/doctor.user-sex'))
                                            ->color('info')
                                            ->formatStateUsing(function (UserSexEnum $state) {
                                                return $state === UserSexEnum::MAN ? __('doctor/doctor.user-sex-man') :
                                                    __('doctor/doctor.user-sex-woman');
                                            }),
                                        Components\TextEntry::make('birthday')
                                            ->date(session()->get('local') !== 'en' ? 'd M Y' : null)
                                            ->label(__('doctor/patient.user-birthday')),
                                        Components\TextEntry::make('addresse')
                                            ->label(__('doctor/patient.user-addresse')),
                                        Components\TextEntry::make('city.name')
                                            ->label(__('doctor/patient.user-city')),
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
            PatientResource\RelationManagers\PatientRecordsRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => PatientResource\Pages\ListPatients::route('/'),
            // 'create' => PatientResource\Pages\CreatePatient::route('/create'),
            // 'edit' => PatientResource\Pages\EditPatient::route('/{record}/edit'),
            'view' => PatientResource\Pages\ViewPatient::route('/{record}'),
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->whereHas('appointments', function ($query) {
                $doctorId = auth()->user()->doctor->id;

                return $query->where('doctor_id', $doctorId)
                    ->where('status', AppointmentStatusEnum::FINISHED->value)
                    ->orWhere('status', AppointmentStatusEnum::ACCEPTED->value)
                    ->where('date_appointment', '<=', now());
            });
    }

    public static function canCreate(): bool
    {
        return false;
    }

    public static function canDelete(Model $record): bool
    {
        return false;
    }
}
