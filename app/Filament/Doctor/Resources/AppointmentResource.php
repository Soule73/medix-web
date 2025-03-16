<?php

namespace App\Filament\Doctor\Resources;

use Filament\Forms;
use Filament\Tables;
use Filament\Forms\Form;
use Filament\Tables\Table;
use App\Models\Appointment;
use Filament\Notifications;
use Illuminate\Support\Str;
use App\Enums\User\UserSexEnum;
use Filament\Infolists\Infolist;
use Filament\Resources\Resource;
use Filament\Infolists\Components;
use Illuminate\Support\Facades\Auth;
use Illuminate\Database\Eloquent\Builder;
use App\Enums\Appointment\AppointmentStatusEnum;
use App\Jobs\SendAppointmentStausNotificationToPatient;
use App\Filament\Doctor\Resources\AppointmentResource\Pages;

class AppointmentResource extends Resource
{
    public static string $resource = Appointment::class;

    protected static ?int $navigationSort = 3;

    protected static ?string $model = Appointment::class;

    protected static ?string $navigationIcon = 'heroicon-o-calendar-days';

    public static function getNavigationGroup(): ?string
    {
        return __('doctor/patient.navigation-group');
    }

    public static function getModelLabel(): string
    {
        return __('doctor/appointment.model-label');
    }

    public static function getPluralLabel(): string
    {
        return __('doctor/appointment.model-label-plural');
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Fieldset::make('patient')
                    ->relationship('patient')
                    ->label(__('doctor/patient.model-label'))
                    ->schema([
                        Forms\Components\TextInput::make('id_cnss')
                            ->label(__('doctor/patient.id-cnss')),
                        Forms\Components\DatePicker::make('birthday')
                            ->prefixIcon('heroicon-o-calendar-days')
                            ->native(false)
                            ->label(__('doctor/patient.user-birthday')),
                    ]),
                Forms\Components\Fieldset::make('appointment')
                    ->label(__('doctor/appointment.model-label'))
                    ->schema([
                        Forms\Components\TextInput::make('type')->label(__('doctor/appointment.type')),
                        Forms\Components\Checkbox::make('payed')->label(__('doctor/appointment.payed'))
                            ->inline(false)
                            ->helperText(fn($state) => $state ? __('doctor/appointment.form-payed-confirmed') : __('doctor/appointment.form-confirm-payed')),
                        Forms\Components\Textarea::make('motif')->label(__('doctor/appointment.motif'))
                            ->columnSpanFull(),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table

            ->emptyStateIcon('heroicon-o-calendar-days')
            ->emptyStateHeading(__(__('doctor/appointment.table-empty-state-heading')))
            ->emptyStateDescription(__('doctor/appointment.table-empty-state-description'))

            ->columns([
                Tables\Columns\ImageColumn::make('patient.user.avatar')
                    ->disk('local')
                    ->circular()
                    ->label('')
                    ->placeholder(__('doctor/patient.no-photo')),
                Tables\Columns\TextColumn::make('patient.user_fullname')
                    ->label(__('doctor/doctor.full-name'))
                    ->searchable(),
                Tables\Columns\TextColumn::make('date_appointment')
                    ->sortable()
                    ->dateTime(session()->get('local') !== 'en' ? 'd M Y H:i:s' : null)
                    ->label(__('doctor/appointment.date')),
                Tables\Columns\TextColumn::make('status')
                    ->description(fn(Appointment $record) => $record->date_appointment < now() ? __('doctor/appointment.passed') : null)
                    ->label(__('doctor/doctor.doctor-status'))
                    ->badge()
                    ->formatStateUsing(function (AppointmentStatusEnum $state, Appointment $appointment) {
                        $text = null;
                        switch ($state) {
                            case AppointmentStatusEnum::DENIED:
                                $text = __('doctor/appointment.refused');
                                break;
                            case AppointmentStatusEnum::ACCEPTED:
                                $text = __('doctor/appointment.accepted');
                                break;
                            case AppointmentStatusEnum::FINISHED:
                                $text = __('doctor/appointment.finished');
                                break;
                            default:
                                $text = __('doctor/appointment.pending');
                                break;
                        }

                        return $text;
                    })
                    ->color(function (AppointmentStatusEnum $state) {
                        $color = null;
                        switch ($state) {
                            case AppointmentStatusEnum::DENIED:
                                $color = 'danger';
                                break;
                            case AppointmentStatusEnum::ACCEPTED:
                                $color = 'success';
                                break;
                            case AppointmentStatusEnum::FINISHED:
                                $color = 'info';
                                break;
                            default:
                                $color = 'warning';
                                break;
                        }

                        return $color;
                    }),
                Tables\Columns\TextColumn::make('work_place.name')
                    ->searchable()
                    ->label(__('doctor/relation/work-place.modelLabel')),
                Tables\Columns\TextColumn::make('created_at')
                    ->sortable()
                    ->dateTime(session()->get('local') !== 'en' ? 'd M Y H:i:s' : null)
                    ->label(__('doctor/appointment.created-at')),
                Tables\Columns\IconColumn::make('payed')
                    ->boolean()
                    ->tooltip(fn($state) => $state ? __('doctor/appointment.payed-yes') :
                        __('doctor/appointment.payed-no'))
                    ->label(__('doctor/appointment.payed')),

            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->options([
                        AppointmentStatusEnum::ACCEPTED->value => __('doctor/appointment.accepted'),
                        AppointmentStatusEnum::PENDING->value => __('doctor/appointment.pending'),
                        AppointmentStatusEnum::DENIED->value => __('doctor/appointment.refused'),
                        AppointmentStatusEnum::FINISHED->value => __('doctor/appointment.finished'),
                    ]),
                Tables\Filters\TernaryFilter::make('payed')
                    ->label(__('doctor/appointment.payed')),
            ])
            ->actions([
                Tables\Actions\ActionGroup::make(
                    [
                        Tables\Actions\ViewAction::make()->label(__('doctor/appointment.action-view')),
                        Tables\Actions\DeleteAction::make()
                            ->visible(fn(Appointment $record) => $record->date_appointment < now() && $record->status === AppointmentStatusEnum::PENDING),
                        Tables\Actions\Action::make('accept')
                            ->label(__('doctor/appointment.action-accepted'))
                            ->requiresConfirmation()
                            ->deselectRecordsAfterCompletion()
                            ->form(function (Form $form) {
                                return $form->schema(
                                    [
                                        Forms\Components\Textarea::make('accepted_message')
                                            ->autofocus()
                                            ->autosize()
                                            ->maxLength(1500)
                                            ->placeholder(__('doctor/appointment.form-accepted-action'))
                                            ->label(__('doctor/appointment.form-accepted-action-label')),
                                    ]
                                );
                            })
                            ->action(function (Appointment $appointment, array $data) {
                                if ($accepted_message = $data['accepted_message']) {
                                    $appointment->accepted_message = $accepted_message;
                                }
                                $appointment->status = AppointmentStatusEnum::ACCEPTED->value;
                                $appointment->save();
                                Notifications\Notification::make()
                                    ->title(__('doctor/appointment.accepted-notification'))
                                    ->icon('heroicon-s-check-circle')
                                    ->success()
                                    ->send();
                                SendAppointmentStausNotificationToPatient::dispatch(appointment: $appointment);
                            })
                            ->color('success')
                            ->visible(
                                fn(Appointment $record) => $record->date_appointment > now()
                                    && $record->status !== AppointmentStatusEnum::ACCEPTED
                            )
                            ->icon('heroicon-s-check-circle'),

                        Tables\Actions\Action::make('refuse')
                            ->label(__('doctor/appointment.action-refused'))
                            ->visible(
                                fn(Appointment $record) => $record->date_appointment > now()
                                    && $record->status !== AppointmentStatusEnum::DENIED
                            )
                            ->deselectRecordsAfterCompletion()
                            ->requiresConfirmation()
                            ->action(function (Appointment $records, array $data) {
                                $records->status = AppointmentStatusEnum::DENIED->value;
                                $records->reason_for_refusal = $data['reason_for_refusal'];
                                $records->save();
                                Notifications\Notification::make()
                                    ->title(__('doctor/appointment.refused-notification'))
                                    ->icon('heroicon-s-x-circle')
                                    ->danger()
                                    ->send();
                                SendAppointmentStausNotificationToPatient::dispatch(appointment: $records);
                            })
                            ->form(function (Form $form) {
                                return $form->schema(
                                    [
                                        Forms\Components\Textarea::make('reason_for_refusal')
                                            ->required()
                                            ->autofocus()
                                            ->autosize()
                                            ->maxLength(1500)
                                            ->placeholder(__('doctor/appointment.form-refused-action'))
                                            ->label(__('doctor/appointment.form-refused-action-label'))
                                            ->validationMessages([
                                                'required' => __('doctor/appointment.form-refused-action'),
                                            ]),
                                    ]
                                );
                            })
                            ->color('danger')
                            ->icon('heroicon-s-x-circle'),

                        // finished
                        Tables\Actions\Action::make('finished')
                            ->label(__('doctor/appointment.finished'))
                            ->visible(
                                fn(Appointment $record) => $record->date_appointment <= now()
                                    && $record->status === AppointmentStatusEnum::ACCEPTED
                            )
                            ->deselectRecordsAfterCompletion()
                            ->requiresConfirmation()
                            ->action(function (Appointment $records) {
                                $records->status = AppointmentStatusEnum::FINISHED->value;
                                $records->save();
                                Notifications\Notification::make()
                                    ->title(__('doctor/appointment.finished-notification'))
                                    ->icon('heroicon-s-check-circle')
                                    ->info()
                                    ->send();
                                SendAppointmentStausNotificationToPatient::dispatch(appointment: $records);
                            })
                            ->color('info')
                            ->icon('heroicon-s-check-circle'),

                    ]
                ),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),

                ]),
            ])->defaultSort('date_appointment', 'desc');
    }

    public static function infolist(Infolist $infolist): Infolist
    {
        return $infolist
            ->schema([

                Components\Section::make(__('doctor/appointment.infolist-title-section'))
                    ->icon('heroicon-o-calendar-days')
                    ->schema([
                        Components\Grid::make(2)
                            ->schema([
                                Components\Group::make([
                                    Components\TextEntry::make('type')
                                        ->placeholder(__('doctor/appointment.not-specificed'))
                                        ->label(__('doctor/appointment.type')),
                                    Components\TextEntry::make('motif')
                                        ->placeholder(__('doctor/appointment.not-specificed'))
                                        ->label(__('doctor/appointment.motif')),
                                    Components\TextEntry::make('work_place.name')->label(__('doctor/appointment.work-place')),

                                ]),
                                Components\Group::make([
                                    Components\TextEntry::make('status')
                                        ->label(__('doctor/doctor.doctor-status'))
                                        ->badge()
                                        ->formatStateUsing(function (AppointmentStatusEnum $state) {
                                            $text = null;
                                            switch ($state) {
                                                case AppointmentStatusEnum::DENIED:
                                                    $text = __('doctor/appointment.refused');
                                                    break;
                                                case AppointmentStatusEnum::ACCEPTED:
                                                    $text = __('doctor/appointment.accepted');
                                                    break;
                                                case AppointmentStatusEnum::FINISHED:
                                                    $text = __('doctor/appointment.finished');
                                                    break;
                                                default:
                                                    $text = __('doctor/appointment.pending');
                                                    break;
                                            }

                                            return $text;
                                        })
                                        ->color(function (AppointmentStatusEnum $state) {
                                            $color = null;
                                            switch ($state) {
                                                case AppointmentStatusEnum::DENIED:
                                                    $color = 'danger';
                                                    break;
                                                case AppointmentStatusEnum::ACCEPTED:
                                                    $color = 'success';
                                                    break;
                                                case AppointmentStatusEnum::FINISHED:
                                                    $color = 'info';
                                                    break;
                                                default:
                                                    $color = 'warning';
                                                    break;
                                            }

                                            return $color;
                                        }),
                                    Components\TextEntry::make('date_appointment')
                                        ->visible(function (Appointment $record): bool {
                                            return !$record->reschedule_date;
                                        })
                                        ->dateTime(session()->get('local') !== 'en' ? 'd M Y H:i:s' : null)
                                        ->label(__('doctor/appointment.date')),
                                    Components\TextEntry::make('reschedule_date')
                                        ->visible(function (Appointment $record): bool {
                                            return $record->reschedule_date !== null;
                                        })
                                        ->dateTime(session()->get('local') !== 'en' ? 'd M Y H:i:s' : null)
                                        ->label(fn(Appointment $record) => $record->add_by_doctor ? __('doctor/appointment.follow-up-appointment-scheduled-by-you') : ('doctor/appointment.you-postponed-the-date-to'))
                                        ->suffix(' (' . __('doctor/appointment.waiting-for-confirmation-from-the-patient') . ')'),

                                ]),
                                Components\Group::make([
                                    Components\Section::make(__('doctor/appointment.payment'))
                                        ->icon('heroicon-o-credit-card')
                                        ->schema([
                                            Components\Grid::make(2)->schema([
                                                Components\IconEntry::make('payed')
                                                    ->boolean()->label(fn($state) => $state ? __('doctor/appointment.payed') :
                                                        __('doctor/appointment.payed-no')),
                                                Components\TextEntry::make('amount')
                                                    ->formatStateUsing(fn($state) => $state > 0 ? $state : __('doctor/appointment.waiting-for-payment'))
                                                    // ->placeholder('Non payé')
                                                    ->suffix(fn($state) => $state > 0 ? 'mru' : null)
                                                    ->label(__('doctor/appointment.amount')),
                                            ]),
                                        ])->collapsed(),
                                ]),
                            ]),
                    ])->collapsible(),
                Components\Section::make(__('doctor/appointment.payment-info'))
                    ->icon('heroicon-o-user-circle')
                    ->schema([
                        Components\Split::make([
                            Components\Grid::make(2)
                                ->schema([
                                    Components\Group::make([
                                        Components\TextEntry::make('patient.user_fullname')->label(__('doctor/doctor.full-name')),
                                        Components\TextEntry::make('patient.user.email')->label(__('doctor/doctor.user-email')),
                                        Components\TextEntry::make('patient.user.phone')->label(__('doctor/doctor.user-phone')),
                                        Components\TextEntry::make('patient.id_cnss')
                                            ->formatStateUsing(fn($state) => Str::upper($state))
                                            ->label(__('doctor/patient.id-cnss')),

                                    ]),
                                    Components\Group::make([
                                        Components\TextEntry::make('patient.user.sex')
                                            ->label(__('doctor/doctor.user-sex'))
                                            ->color('info')
                                            ->formatStateUsing(function (UserSexEnum $state) {
                                                return $state === UserSexEnum::MAN ? __('doctor/doctor.user-sex-man') :
                                                    __('doctor/doctor.user-sex-woman');
                                            }),
                                        Components\TextEntry::make('patient.birthday')
                                            ->date(session()->get('local') !== 'en' ? 'd M Y' : null)
                                            ->label(__('doctor/patient.user-birthday')),
                                        Components\TextEntry::make('patient.addresse')
                                            ->label(__('doctor/patient.user-addresse')),
                                    ]),
                                ]),
                            Components\ImageEntry::make('patient.user.avatar')
                                ->hiddenLabel()
                                ->disk('local')
                                ->circular()
                                ->grow(false),
                        ])->from('lg'),
                    ])->collapsed(),

            ]);
    }

    public static function getRelations(): array
    {
        return [
            AppointmentResource\RelationManagers\PatientRecordsRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListAppointments::route('/'),
            'view' => Pages\ViewAppointment::route('/{record}'),
            // 'create' => Pages\CreateAppointment::route('/create'),
            // 'edit' => Pages\EditAppointment::route('/{record}/edit'),
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->where('doctor_id', Auth::user()->doctor->id);
    }

    public static function canCreate(): bool
    {
        return false;
    }
}
