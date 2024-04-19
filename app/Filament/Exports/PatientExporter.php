<?php

namespace App\Filament\Exports;

use App\Models\Patient;
use Filament\Actions\Exports\ExportColumn;
use Filament\Actions\Exports\Exporter;
use Filament\Actions\Exports\Models\Export;
use Illuminate\Support\Str;

class PatientExporter extends Exporter
{
    protected static ?string $model = Patient::class;

    public static function getColumns(): array
    {
        return [
            ExportColumn::make('id_cnss')
                ->formatStateUsing(fn ($state) => Str::upper($state))
                ->label(__('doctor/patient.id-cnss')),
            ExportColumn::make('user_fullname')->label(__('doctor/doctor.full-name')),
            ExportColumn::make('user.phone')->label(__('doctor/doctor.user-phone')),
            ExportColumn::make('birthday')
                ->label(__('doctor/patient.user-birthday')),
            ExportColumn::make('addresse')
                ->label(__('doctor/patient.user-addresse')),
            ExportColumn::make('city.name')
                ->label(__('doctor/patient.user-city')),
        ];
    }

    public static function getCompletedNotificationBody(Export $export): string
    {
        $body = __('doctor/patient.patient-export-success-notification', ['rows' => number_format($export->successful_rows)]);
        // $body = 'Your patient export has completed and ' . number_format($export->successful_rows) . ' ' . str('row')->plural($export->successful_rows) . ' exported.';

        if ($failedRowsCount = $export->getFailedRowsCount()) {
            $body .= ' '.__('doctor/patient.patient-export-failed-notification', ['rows' => number_format($failedRowsCount)]);
            // $body .= ' ' . number_format($failedRowsCount) . ' ' . str('row')->plural($failedRowsCount) . ' failed to export.';
        }

        return $body;
    }
}
