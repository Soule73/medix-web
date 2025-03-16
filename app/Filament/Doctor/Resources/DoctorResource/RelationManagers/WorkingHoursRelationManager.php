<?php

namespace App\Filament\Doctor\Resources\DoctorResource\RelationManagers;

use App\Models\Day;
use App\Models\WorkingHour;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Forms\Get;
use Filament\Forms\Set;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Support\Enums\MaxWidth;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;

class WorkingHoursRelationManager extends RelationManager
{
    // protected static ?string $modelLabel = "Heures de travail";

    // protected static ?string $label = "Lieux des travails";
    // protected static ?string $title = "Heure de travail";

    protected static string $relationship = 'working_hours';

    public static function getTitle($ownerRecord, string $pageClass): string
    {
        return __('doctor/relation/working-hour.modelLabel');
    }

    protected static function getModelLabel(): ?string
    {
        return __('doctor/relation/working-hour.modelLabel');
    }

    public function form(Form $form): Form
    {
        return $form

            ->schema([
                Forms\Components\Grid::make(1)
                    ->schema([
                        Forms\Components\Select::make('work_place_id')
                            ->label(__('doctor/relation/work-place.modelLabel'))
                            ->createOptionModalHeading('Ajout un lieu de travail')
                            ->relationship(
                                'work_place',
                                'name',
                                fn (Builder $query) => $query->where('doctor_id', auth()->user()->doctor->id)
                            )
                            ->searchable()
                            ->preload()
                            ->createOptionForm([

                                Forms\Components\Hidden::make('doctor_id'),
                                Forms\Components\TextInput::make('name')->label('Nom du lieu de travail')
                                    ->required()
                                    ->validationMessages([
                                        'required' => __('doctor/relation/work-place.form-name-required'),
                                    ])
                                    ->live()
                                    ->afterStateUpdated(fn (Set $set, ?string $state) => $set('doctor_id', auth()->user()->doctor->id))
                                    ->maxLength(255),

                                Forms\Components\TextInput::make('address')->label(__('doctor/relation/work-place.adress'))
                                    ->required()
                                    ->validationMessages([
                                        'required' => __('doctor/relation/work-place.form-adress-required'),
                                    ])
                                    ->maxLength(255),
                                Forms\Components\Select::make('city_id')->label(__('doctor/relation/work-place.city'))
                                    ->relationship('city', 'name')
                                    ->preload()
                                    ->searchable()
                                    ->required()
                                    ->validationMessages([
                                        'required' => __('doctor/relation/work-place.form-city-required'),
                                    ]),
                                Forms\Components\Section::make(__('doctor/relation/work-place.form-section-title'))
                                    ->description(__('doctor/relation/work-place.form-section-description'))
                                    ->schema([
                                        Forms\Components\Split::make(
                                            [Forms\Components\Grid::make(2)
                                                ->schema([
                                                    Forms\Components\TextInput::make(__('doctor/relation/work-place.longitude'))->numeric(),
                                                    Forms\Components\TextInput::make(__('doctor/relation/work-place.latitude'))
                                                        ->numeric(),
                                                ])]

                                        ),
                                    ]),
                            ]),
                        Forms\Components\Select::make('day_id')
                            ->options(Day::orderBy('id')->pluck('id', 'name')->mapWithKeys(function ($id, $name): array {
                                return [$id => __('day.'.$name)];
                            })->toArray())
                            ->preload()
                            ->label(__('doctor/relation/working-hour.day'))
                            ->required(),
                        Forms\Components\TimePicker::make('start_at')
                            ->label(__('doctor/relation/working-hour.start_at'))
                            ->required()
                            ->before('end_at')
                            ->rules([
                                function (Get $get) {
                                    return function ($attribute, $value, $fail) use ($get) {
                                        $day_id = $get('day_id');
                                        $doctor_id = auth()->user()->doctor->id;
                                        $existingHours = WorkingHour::where('doctor_id', $doctor_id)
                                            ->where('day_id', $day_id)
                                            ->get();
                                        foreach ($existingHours as $hour) {
                                            if ($value >= $hour->start_at && $value <= $hour->end_at) {
                                                return $fail(__('doctor/relation/working-hour.overlap_error'));
                                            }
                                        }
                                    };
                                },
                            ])
                            ->validationMessages(['before' => __('doctor/relation/working-hour.end-at-before')]),
                        Forms\Components\TimePicker::make('end_at')->label(__('doctor/relation/working-hour.end_at'))
                            ->required()
                            ->validationMessages(['after' => __('doctor/relation/working-hour.start-at-after')])
                            ->after('start_at')
                            ->rules([
                                function (Get $get) {
                                    return function ($attribute, $value, $fail) use ($get) {
                                        $day_id = $get('day_id');
                                        $doctor_id = auth()->user()->doctor->id;
                                        $existingHours = WorkingHour::where('day_id', $day_id)
                                            ->where('doctor_id', $doctor_id)
                                            ->get();
                                        foreach ($existingHours as $hour) {
                                            if ($value <= $hour->end_at && $value >= $hour->start_at) {
                                                return $fail(__('doctor/relation/working-hour.overlap_error'));
                                            }
                                        }
                                    };
                                },
                            ]),
                    ]),

            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->description(__('doctor/relation/working-hour.table-emptyStateDescription'))
            ->emptyStateIcon('heroicon-o-calendar-days')
            ->paginated(true)
            ->emptyStateHeading(__('doctor/relation/working-hour.table-emptyStateHeading'))
            ->emptyStateDescription(__('doctor/relation/working-hour.table-emptyStateDescription'))
            // ->recordTitleAttribute('start_at')
            ->columns([
                Tables\Columns\TextColumn::make('day.name')->label(__('doctor/relation/working-hour.day'))
                    ->formatStateUsing(fn (string $state): string => __("day.{$state}"))
                    ->sortable(true, function ($query, $direction) {
                        return $query->orderBy('day_id', $direction);
                    }),

                Tables\Columns\TextColumn::make('work_place.name')->label(__('doctor/relation/work-place.modelLabel'))
                    ->searchable(),
                Tables\Columns\TextColumn::make('start_at')->time()->label(__('doctor/relation/working-hour.start_at')),
                Tables\Columns\TextColumn::make('end_at')->time()->label(__('doctor/relation/working-hour.end_at')),
            ])->defaultSort('day_id')
            ->filters([
                Tables\Filters\Filter::make('day_name')
                    ->form([
                        Forms\Components\Select::make('day_name')
                            ->label(__('doctor/relation/working-hour.day'))
                            ->multiple()
                            ->options([
                                'monday' => __('day.monday'),
                                'tuesday' => __('day.tuesday'),
                                'wednesday' => __('day.wednesday'),
                                'thursday' => __('day.thursday'),
                                'friday' => __('day.friday'),
                                'saturday' => __('day.saturday'),
                                'sunday' => __('day.sunday'),

                            ]),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['day_name'],
                                fn (Builder $query, $date): Builder => $query
                                    ->whereHas('day', function ($query) use ($date) {
                                        return $query->whereIn('name', $date)
                                            ->orderBy('id');
                                    }),
                            );
                    }),
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make()
                    ->modalWidth(MaxWidth::Large)
                    ->modalSubmitActionLabel(__('actions.add'))
                    ->modalHeading(__('doctor/doctor.add-a-time-slot'))
                    ->modalDescription(__('doctor/relation/working-hour.section-description'))
                    ->icon('heroicon-o-calendar-days')
                    ->label(__('actions.add')),
                Tables\Actions\Action::make(__('doctor/relation/working-hour.timetable'))
                    ->modalContent(fn (): View => view('filament.doctor.components.schedule-print'))
                    ->modalSubmitAction(false)
                    ->modalCancelActionLabel(__('doctor/relation/document-for-validation.cloe-btn'))
                    ->modalCloseButton()
                    ->modalWidth(MaxWidth::FiveExtraLarge)
                    ->modalHeading(__('doctor/relation/working-hour.timetable'))
                    ->modelLabel(__('doctor/relation/working-hour.timetable')),
            ])
            ->actions([
                Tables\Actions\EditAction::make()
                    ->modalWidth(MaxWidth::Large),

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
