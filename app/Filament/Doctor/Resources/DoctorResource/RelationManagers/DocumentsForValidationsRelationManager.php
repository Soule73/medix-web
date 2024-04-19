<?php

namespace App\Filament\Doctor\Resources\DoctorResource\RelationManagers;

use App\Enums\Doctor\DocumentForValidationEnum;
use App\Enums\DocumentsForValidation\DocumentsForValidationStatusEnum;
use App\Models\DocumentsForValidation;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Support\Enums\MaxWidth;
use Filament\Tables;
use Filament\Tables\Columns\Layout\View;
use Filament\Tables\Table;
use Illuminate\Validation\Rule;

class DocumentsForValidationsRelationManager extends RelationManager
{
    // protected static ?string $modelLabel = "Documents pour la validation";
    protected static string $relationship = 'documents_for_validations';
    // protected static ?string $title = "Documents pour la validation";

    public static function getTitle($ownerRecord, string $pageClass): string
    {
        return __('doctor/relation/document-for-validation.modelLabel');
    }

    protected static function getModelLabel(): ?string
    {
        return __('doctor/relation/document-for-validation.modelLabel');
    }

    public function form(Form $form): Form
    {
        return $form

            ->schema([
                Forms\Components\Grid::make(1)
                    ->schema([
                        Forms\Components\Select::make('name')->label(__('doctor/relation/document-for-validation.form-section-name'))
                            ->options([
                                DocumentForValidationEnum::FRONT_IDENTITY_CARD->value => __('doctor/relation/document-for-validation.front_identity_card'),
                                DocumentForValidationEnum::IDENTITY_CARD_BACK->value => __('doctor/relation/document-for-validation.identity_card_back'),
                                DocumentForValidationEnum::PASSPORT->value => 'Passport',
                                DocumentForValidationEnum::CERTIFICATE_OF_REGISTRATION->value => __('doctor/relation/document-for-validation.certificate_of_registration'),
                            ])
                            ->searchable()
                            ->required()
                            ->rule(Rule::unique('documents_for_validations', 'name')->where(function ($query) {
                                return $query->where('doctor_id', auth()->user()->doctor->id);
                            }))
                            ->validationMessages([
                                'unique' => __('doctor/relation/document-for-validation.form-documents-name-unique'),
                                'required' => __('doctor/relation/document-for-validation.form-documents-name-required'),
                            ]),
                        Forms\Components\FileUpload::make('path')->label(__('doctor/relation/document-for-validation.document'))
                            ->acceptedFileTypes(['application/pdf'])
                            ->disk('local')
                            ->required()
                            ->directory('public/doctor/documents')
                            ->visibility('public')
                            ->validationMessages([
                                'required' => __('doctor/relation/document-for-validation.form-documents-document-required'),
                            ]),
                    ])


            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->description(View::make('filament.doctor.components.description'))
            ->emptyStateIcon('heroicon-o-document-text')
            ->emptyStateHeading(__('doctor/relation/document-for-validation.table-emptyStateHeading'))
            ->emptyStateDescription(__('doctor/relation/document-for-validation.table-emptyStateDescription'))
            // ->recordTitleAttribute('name')
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->formatStateUsing(fn (string $state): string => __("doctor/relation/document-for-validation.$state"))
                    ->label(__('doctor/relation/document-for-validation.document')),
                Tables\Columns\TextColumn::make('status')->label(__('doctor/relation/document-for-validation.document-status'))
                    ->color(function (DocumentsForValidationStatusEnum $state) {
                        $color = null;
                        switch ($state) {
                            case DocumentsForValidationStatusEnum::NOTVALIDATED:
                                $color = 'danger';
                                break;
                            case DocumentsForValidationStatusEnum::VALIDATED:
                                $color = 'success';
                                break;
                            default:
                                $color = 'info';
                                break;
                        }

                        return $color;
                    })
                    ->formatStateUsing(function (DocumentsForValidationStatusEnum $state) {
                        $text = null;
                        switch ($state) {
                            case DocumentsForValidationStatusEnum::NOTVALIDATED:
                                $text = __('doctor/doctor.doctor-status-notvalidated');
                                break;
                            case DocumentsForValidationStatusEnum::VALIDATED:
                                $text = __('doctor/doctor.doctor-status-validated');
                                break;
                            default:
                                $text = __('doctor/relation/document-for-validation.document-status-pending');
                                break;
                        }

                        return $text;
                    })
                    ->badge(),
                Tables\Columns\TextColumn::make('message')->label(__('doctor/relation/document-for-validation.document-message'))
                    ->placeholder(__('doctor/relation/document-for-validation.no-sightings')),

            ])
            ->filters([
                //
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make()
                    ->modalHeading(__('doctor/relation/document-for-validation.form-section-title'))
                    ->modalDescription(__('doctor/relation/document-for-validation.form-section-description'))
                    ->modalSubmitActionLabel(__('actions.add'))
                    ->modalWidth(MaxWidth::TwoExtraLarge)
                    ->mutateFormDataUsing(function ($data) {
                        $data['doctor_id'] = auth()->user()->doctor->id;

                        return $data;
                    })
                    ->label(__('actions.add'))->icon('heroicon-o-plus'),

            ])
            ->actions([
                Tables\Actions\EditAction::make()
                    ->visible(fn (DocumentsForValidation $record) => $record->status !== DocumentsForValidationStatusEnum::VALIDATED),
                Tables\Actions\DeleteAction::make()
                    ->visible(fn (DocumentsForValidation $record) => $record->status !== DocumentsForValidationStatusEnum::VALIDATED),
                Tables\Actions\Action::make(__('doctor/relation/document-for-validation.open-document'))
                    ->modalContent(
                        function (DocumentsForValidation $record) {
                            return view(
                                'filament.doctor.components.open-pdf',
                                ['document' => $record],
                            );
                        }
                    )
                    ->modalSubmitAction(false)
                    ->modalCancelActionLabel(__('doctor/relation/document-for-validation.cloe-btn'))
                    ->modalCloseButton()
                    ->modalHeading(fn (DocumentsForValidation $state) => __("doctor/relation/document-for-validation.$state->name"))
                    ->modelLabel(fn (DocumentsForValidation $state) => __("doctor/relation/document-for-validation.$state->name"))
                    ->modalWidth(MaxWidth::FiveExtraLarge),

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
