<?php

namespace App\Filament\Doctor\Resources\DoctorResource\RelationManagers;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Support\Enums\MaxWidth;
use Filament\Tables;
use Filament\Tables\Table;

class QualificationsRelationManager extends RelationManager
{
    protected static string $relationship = 'qualifications';
    // protected static ?string $label = "Qualifications";
    // protected static ?string $title = "Qualifications";
    // protected static ?string $modelLabel = "Qualification";

    public static function getTitle($ownerRecord, string $pageClass): string
    {
        return __('doctor/relation/qualification.modelLabel');
    }

    protected static function getModelLabel(): ?string
    {
        return __('doctor/relation/qualification.modelLabel');
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Grid::make(1)
                    ->schema([
                        Forms\Components\TextInput::make('name')->name(__('doctor/doctor.full-name'))
                            ->required()
                            ->validationMessages([
                                'required' => __('doctor/relation/qualification.validation-qualification-name-required'),
                            ])
                            ->maxLength(255),
                        Forms\Components\TextInput::make('institute')->name(__('doctor/relation/qualification.form-institute'))
                            ->required()
                            ->validationMessages([
                                'required' => __('doctor/relation/qualification.validation-institute-required'),
                            ])
                            ->maxLength(255),
                        Forms\Components\DatePicker::make('procurement_date')->name(__('doctor/relation/qualification.form-procurement-date'))
                            ->required()
                            ->validationMessages([
                                'required' => __('doctor/relation/qualification.validation-procurement-date-required'),
                            ]),

                    ]),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->emptyStateIcon('heroicon-o-academic-cap')
            ->emptyStateHeading(__('doctor/relation/qualification.table-emptyStateHeading'))
            ->emptyStateDescription(__('doctor/relation/qualification.table-emptyStateDescription'))
            // ->recordTitleAttribute('name')
            ->emptyStateActions([
                Tables\Actions\CreateAction::make()
                    ->mutateFormDataUsing(function ($data) {
                        $data['doctor_id'] = auth()->user()->doctor->id;

                        return $data;
                    })
                    ->label('Ajouter')->icon('heroicon-o-plus'),
            ])
            ->paginated(true)
            ->columns([
                Tables\Columns\TextColumn::make('name')->label(__('doctor/doctor.full-name')),
                Tables\Columns\TextColumn::make('institute')->label(__('doctor/relation/qualification.form-institute')),
                Tables\Columns\TextColumn::make('procurement_date')
                    ->label(__('doctor/relation/qualification.form-procurement-date'))
                    ->date(session()->get('local') !== 'en' ? 'd M Y' : null)->badge()->color('info'),
            ])
            ->filters([
                //
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make()
                    ->modalSubmitActionLabel(__('actions.add'))
                    ->modalWidth(MaxWidth::ExtraLarge)
                    ->modalHeading(__('doctor/relation/qualification.modelLabel'))
                    ->modalDescription(__('doctor/relation/qualification.form-description'))
                    ->mutateFormDataUsing(function ($data) {
                        $data['doctor_id'] = auth()->user()->doctor->id;

                        return $data;
                    })
                    ->label(__('actions.add'))->icon('heroicon-o-plus'),
            ])
            ->actions([
                // Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
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
