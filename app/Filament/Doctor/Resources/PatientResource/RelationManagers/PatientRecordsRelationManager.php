<?php

namespace App\Filament\Doctor\Resources\PatientResource\RelationManagers;

use Filament\Forms;
use Filament\Tables;
use Filament\Forms\Form;
use Filament\Tables\Table;
use App\Models\PatientRecord;
use Illuminate\Contracts\View\View;
use Filament\Support\Enums\MaxWidth;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Storage;
use Filament\Resources\RelationManagers\RelationManager;

class PatientRecordsRelationManager extends RelationManager
{
    protected static string $relationship = 'patient_records';

    public static function getTitle($ownerRecord, string $pageClass): string
    {
        return __('doctor/patient.patient-record');
    }

    protected static function getModelLabel(): ?string
    {
        return __('doctor/patient.patient-record');
    }

    public function form(Form $form): Form
    {
        return $form->schema([
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

    public function table(Table $table): Table
    {
        return $table
            ->selectable(false)
            ->paginated()
            ->emptyStateIcon('heroicon-o-document-text')
            ->emptyStateHeading(__('doctor/prescription.no-document-found'))
            ->emptyStateDescription(__('doctor/prescription.you-can-add-prescriptions-observations-diagnoses'))
            // ->recordTitleAttribute('observation')
            ->columns([
                Tables\Columns\TextColumn::make('observation')
                    ->searchable()
                    ->extraAttributes([
                        'class' => 'max-w-sm break-words',
                    ])
                    ->wrap(),
                Tables\Columns\TextColumn::make('created_at')
                    ->searchable()
                    ->sortable()
                    ->label(__('doctor/appointment.created-at'))
                    ->dateTime(session()->get('local') !== 'en' ? 'd M Y H:i:s' : null),
                Tables\Columns\TextColumn::make('doctor.user_fullname')
                    ->searchable(query: function (Builder $query, $search) {
                        return $query->whereHas('doctor', function ($query) use ($search) {
                            $query->whereHas('user', function ($query) use ($search) {
                                $query->where('name', 'like', '%' . $search . '%')
                                    ->orWhere('first_name', 'like', '%' . $search . '%');
                            });
                        });
                    })
                    ->label(__('doctor/doctor.navigation-group')),
            ])
            ->defaultSort('created_at')
            ->filters([
                //
            ])
            // ->headerActions([
            //     Tables\Actions\CreateAction::make()
            //         ->label('Ajouté')
            //         ->icon('heroicon-o-document-text')
            //         ->mutateFormDataUsing(function (array $data) {
            //             $doctorId = auth()->user()->doctor->id;
            //             // dd($doctorId);
            //             $data['doctor_id'] = $doctorId;
            //             return $data;
            //             // dd($data);
            //         }),
            //     Tables\Actions\Action::make('add-file')
            //         ->action(function (array $data) {
            //             $this->ownerRecord->patient_records()
            //                 ->create($data);
            //             Notification::make('success')
            //                 ->success()
            //                 ->title('Fichier ajouté')
            //                 ->send();
            //         })
            //         ->form(function (Form $form) {
            //             return $form
            //                 ->schema(
            //                     [
            //                         Forms\Components\FileUpload::make('path')->label('Fichier'),
            //                         Forms\Components\Textarea::make('observation')
            //                             ->autosize()
            //                             // ->required()
            //                             ->columnSpanFull()
            //                         // ->maxLength(255)
            //                         ,
            //                         Forms\Components\TextInput::make('diagnostic')
            //                             // ->autosize()
            //                             // ->required()
            //                             ->label('Diagnostique')
            //                             ->columnSpanFull()
            //                         // ->maxLength(255)
            //                         ,
            //                     ]
            //                 );
            //         })
            // ])
            ->actions([

                Tables\Actions\Action::make('view-prescription')->label(__('doctor/prescription.see-preinscription'))
                    // ->action(fn (PatientRecord $record) => $record->advance())
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
                        ->before(function (PatientRecord $record) {
                            if ($record->path && Storage::fileExists($record->path)) {
                                Storage::delete($record->path);
                            }
                        }),

                ])
                    ->visible(fn (PatientRecord $patientRecord) => $patientRecord->doctor_id === auth()->user()->doctor->id)
                    ->color('gray'),
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
