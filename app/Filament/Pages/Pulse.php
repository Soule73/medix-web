<?php

namespace App\Filament\Pages;

use Filament\Pages\Page;

class Pulse extends Page
{
    protected static ?int $navigationSort = 3;

    protected static ?string $navigationIcon = 'heroicon-o-server-stack';

    protected static string $view = 'filament.pages.pulse';

    public static function getNavigationLabel(): string
    {
        return __('pulse.system-status');
    }

    public function getTitle(): string
    {
        return __('pulse.system-status');
    }
}
