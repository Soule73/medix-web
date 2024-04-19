<?php

return [
    App\Providers\AppServiceProvider::class,
    App\Providers\Filament\AdminPanelProvider::class,
    App\Providers\Filament\DoctorPanelProvider::class,

    Barryvdh\Debugbar\ServiceProvider::class,
    Barryvdh\DomPDF\ServiceProvider::class,
    Mccarlosen\LaravelMpdf\LaravelMpdfServiceProvider::class,
    Berkayk\OneSignal\OneSignalServiceProvider::class,
];
