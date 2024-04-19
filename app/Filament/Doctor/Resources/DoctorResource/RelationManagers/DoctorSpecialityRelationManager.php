<?php

namespace App\Filament\Doctor\Resources\DoctorResource\RelationManagers;

use Filament\Forms;
use Filament\Tables;
use Filament\Forms\Form;
use App\Models\Speciality;
use Filament\Tables\Table;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Support\Enums\MaxWidth;

class DoctorSpecialityRelationManager extends RelationManager
{
    protected static string $relationship = 'doctor_speciality';

    public static function getTitle($ownerRecord, string $pageClass): string
    {
        return __('doctor/relation/speciality.modelLabel');
    }

    protected static function getModelLabel(): ?string
    {
        return __('doctor/relation/speciality.modelLabel');
    }
    // protected static ?string $label = "Spécialité";
    // protected static ?string $title = "Spécialités";
    // protected static ?string $modelLabel = "Spécialité";

    public function form(Form $form): Form
    {
        return $form

            ->schema([
                Forms\Components\Grid::make(1)
                    ->schema([
                        Forms\Components\Select::make('speciality_id')
                            // ->relationship(
                            //     'speciality',
                            //     'name',
                            //     function (Builder $query) {
                            //         return $query->whereDoesntHave('doctors', function ($query) {
                            //             return $query->where('doctor_id', auth()->user()->doctor->id);
                            //         });
                            //     }
                            // )
                            ->options(Speciality::whereDoesntHave('doctors', function ($query) {
                                return $query->where('doctor_id', auth()->user()->doctor->id);
                            })
                                ->orderBy('id')->pluck('id', 'name')->mapWithKeys(function ($id, $name): array {
                                    return [$id => __("doctor/relation/speciality.$name.name")];
                                })->toArray())
                            ->required()
                            ->preload()
                            ->label(__('doctor/relation/speciality.modelLabel'))
                            ->rule(Rule::unique('doctor_speciality', 'speciality_id')->where(function ($query) {
                                return $query->where('doctor_id', auth()->user()->doctor->id);
                            }))
                            ->validationMessages([
                                'unique' => __('doctor/relation/speciality.form-validation-speciality-name-unique'),
                            ])
                            ->searchable(),
                    ])
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->emptyStateActions([
                Tables\Actions\CreateAction::make()
                    ->label(__('actions.add'))
                    ->icon('heroicon-o-plus')
                    ->mutateFormDataUsing(function ($data) {
                        $data['doctor_id'] = auth()->user()->doctor->id;

                        return $data;
                    }),
            ])
            ->emptyStateIcon('heroicon-o-bookmark')
            ->emptyStateHeading(__('doctor/relation/speciality.table-emptyStateHeading'))
            ->emptyStateDescription(__('doctor/relation/speciality.table-emptyStateDescription'))
            ->paginated(true)
            ->columns([
                Tables\Columns\TextColumn::make('speciality.name')
                    ->formatStateUsing(fn (string $state): string => __("doctor/relation/speciality.$state.name"))
                    ->label(__('doctor/doctor.full-name'))
                    ->sortable()
                    ->searchable(),
                Tables\Columns\TextColumn::make('speciality.description')
                    ->formatStateUsing(fn (string $state): string => __('doctor/relation/speciality.' . Speciality::where('description', $state)->first()->name . '.description'))
                    ->label(__('doctor/relation/speciality.description')),
            ])->defaultSort('speciality.name', 'asc')
            ->filters([
                //
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make()
                    ->modalWidth(MaxWidth::Large)
                    ->modalSubmitActionLabel(__('actions.add'))
                    ->modalHeading(__('doctor/doctor.add-a-specialty', ['add' => Str::lower(self::getModelLabel())]))
                    ->mutateFormDataUsing(function ($data) {
                        $data['doctor_id'] = auth()->user()->doctor->id;

                        return $data;
                    })
                    ->label(__('actions.add'))->icon('heroicon-o-plus'),
            ])
            ->actions([
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
