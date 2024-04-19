<?php

namespace App\Filament\Doctor\Pages;

use Filament\Pages\Page;

class Calendar extends Page
{
    protected static ?int $navigationSort = 5;

    protected static ?string $navigationIcon = 'heroicon-o-calendar';

    protected static string $view = 'filament.doctor.pages.calendar';

    public static function getNavigationGroup(): ?string
    {
        return __('doctor/patient.navigation-group');
    }

    public static function getNavigationLabel(): string
    {
        return __('doctor/doctor.calendar');
    }

    public function getTitle(): string
    {
        return __('doctor/doctor.calendar');
    }
}
