<?php

namespace App\Filament\Doctor\Resources\AppointmentResource\RelationManagers;

use App\Enums\Appointment\AppointmentStatusEnum;
use App\Models\PatientRecord;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Support\Enums\MaxWidth;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class PatientRecordsRelationManager extends RelationManager
{
    protected static string $relationship = 'patient_records';

    public static function getTitle($ownerRecord, string $pageClass): string
    {
        return __('doctor/patient.appointment-sheet');
    }

    protected static function getModelLabel(): ?string
    {
        return __('doctor/patient.appointment-sheet');
    }

    public function form(Form $form): Form
    {
        return $form

            ->schema([
                Forms\Components\Textarea::make('observation')
                    ->autosize()
                    ->columnSpanFull(),
                Forms\Components\TextInput::make('diagnostic')
                    ->label('Diagnostique')
                    ->columnSpanFull(),
                Forms\Components\Section::make(__('doctor/prescription.title'))
                    ->description(__('doctor/patient.add-medications-to-the-prescription'))
                    ->schema(
                        [
                            Forms\Components\Repeater::make('prescription')
                                ->columnSpanFull()
                                ->addActionLabel(__('doctor/patient.add-another-medication'))
                                ->statePath('prescription')
                                ->label(__('doctor/patient.medications'))
                                ->schema([
                                    Forms\Components\Grid::make(2)
                                        ->schema([
                                            Forms\Components\TextInput::make('medicament')
                                                ->label(__('doctor/patient.medications'))
                                                ->required(),
                                            Forms\Components\TextInput::make('dosage')
                                                ->label(__('doctor/prescription.dosage'))
                                                ->required(),
                                            Forms\Components\TextInput::make('posologie')
                                                ->label(__('doctor/prescription.posologie'))
                                                ->required(),
                                            Forms\Components\TextInput::make('duree')
                                                ->label(__('doctor/prescription.duration'))
                                                ->required(),
                                        ]),
                                ]),
                        ]
                    ),
            ]);
    }

    public function isReadOnly(): bool
    {
        return false;
    }

    public function table(Table $table): Table
    {
        return $table
            ->description(__('doctor/prescription.posible-to-record-prescriptions'))
            ->paginated()
            ->emptyStateIcon('heroicon-o-document-text')
            ->emptyStateHeading(__('doctor/prescription.no-document-found'))
            ->emptyStateDescription(__('doctor/prescription.you-can-add-prescriptions-observations-diagnoses'))
            // ->recordTitleAttribute('observation')
            ->columns([
                Tables\Columns\TextColumn::make('observation')
                    ->extraAttributes([
                        'class' => 'max-w-md break-words',
                    ])
                    ->wrap(),
                Tables\Columns\TextColumn::make('doctor.user_fullname')->label(__('doctor/doctor.navigation-group')),
                Tables\Columns\TextColumn::make('created_at')->label(__('doctor/appointment.created-at'))
                    ->dateTime(session()->get('local') !== 'en' ? 'd M Y H:i:s' : null)
                    ->sortable(),
            ])
            ->filters([
                //
            ])
            ->headerActions([
                Tables\Actions\ActionGroup::make([
                    Tables\Actions\CreateAction::make()->label(__('doctor/prescription.a-prescription'))
                        ->modalHeading(self::getModelLabel())
                        ->icon('heroicon-o-plus')
                        ->visible(fn () => $this->ownerRecord->date_appointment <= now()),
                    Tables\Actions\Action::make('add-file')
                        ->icon('heroicon-o-plus')
                        ->label(__('doctor/prescription.file'))
                        ->mutateFormDataUsing(function ($data) {
                            $doctorId = auth()->user()->doctor->id;
                            $data['doctor_id'] = $doctorId;
                            $data['patient_id'] = $this->ownerRecord->patient_id;
                            $data['type'] = 'file';

                            return $data;
                        })
                        ->action(function (array $data) {
                            $this->ownerRecord->patient_records()
                                ->create($data);
                            Notification::make('success')
                                ->success()
                                ->title(__('doctor/prescription.file-added'))
                                ->duration(9000)
                                ->send();
                        })
                        ->form(function (Form $form) {
                            return $form
                                ->schema(
                                    [
                                        Forms\Components\FileUpload::make('path')
                                            ->disk('local')
                                            ->directory("public/patients/{$this->ownerRecord->patient_id}")
                                            ->visibility('public')
                                            ->label(__('doctor/prescription.file')),
                                        Forms\Components\Textarea::make('observation')
                                            ->autosize()
                                            ->columnSpanFull(),
                                        Forms\Components\TextInput::make('diagnostic')
                                            ->label(__('doctor/prescription.diagnostic'))
                                            ->columnSpanFull(),
                                    ]
                                );
                        }),

                ])->label(__('actions.add'))
                    ->button(),
            ])
            ->actions([
                Tables\Actions\Action::make('view-prescription')->label(__('doctor/prescription.see-preinscription'))
                    ->modalContent(fn (PatientRecord $record): View => view(
                        'filament.doctor.components.prescription-print',
                        ['prescription' => $record],
                    ))
                    ->modalSubmitAction(false)
                    ->modalCancelActionLabel(__('doctor/relation/document-for-validation.cloe-btn'))
                    ->modalCloseButton()
                    ->modalWidth(MaxWidth::FourExtraLarge)
                    ->modalHeading(__('doctor/prescription.title'))
                    ->modelLabel(__('doctor/prescription.title'))
                    ->visible(fn (PatientRecord $record) => $record->type === 'json'),

                Tables\Actions\Action::make(__('doctor/relation/document-for-validation.open-document'))
                    ->visible(fn (PatientRecord $record) => $record->type === 'file')
                    ->modalContent(
                        function (PatientRecord $record) {
                            return view(
                                'filament.doctor.components.open-pdf',
                                ['document' => $record],
                            );
                        }
                    )
                    ->modalSubmitAction(false)
                    ->modalCancelActionLabel(__('doctor/relation/document-for-validation.cloe-btn'))
                    ->modalCloseButton()
                    ->modalHeading(__('doctor/prescription.file'))
                    ->modelLabel(__('doctor/prescription.file'))
                    ->modalWidth(MaxWidth::FiveExtraLarge),

                Tables\Actions\ActionGroup::make([
                    Tables\Actions\EditAction::make()
                        ->visible(fn (PatientRecord $record) => $record->type !== 'file')

                        ->color('info'),
                    Tables\Actions\DeleteAction::make()
                        ->modalHeading(function (PatientRecord $record) {
                            if ($record->path) {
                                return __('actions.delete') . ' ' . 'cette fichier';
                            }
                            return __('actions.delete');
                        })
                        ->before(function (PatientRecord $record) {
                            if ($record->path && Storage::fileExists($record->path)) {
                                Storage::delete($record->path);
                            }
                        })
                ])
                    ->color('gray'),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function canViewForRecord(Model $ownerRecord, string $pageClass): bool
    {
        return ($ownerRecord->status === AppointmentStatusEnum::ACCEPTED ||
            $ownerRecord->status === AppointmentStatusEnum::FINISHED)
            && $ownerRecord->date_appointment <= now();
    }
}
