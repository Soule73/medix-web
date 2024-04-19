<?php

namespace App\Providers\Filament;

use App\Filament\Doctor\Pages\Auth\Register;
use App\Filament\Doctor\Pages\EditProfile;
use App\Filament\Doctor\Resources\AppointmentResource;
use App\Filament\Doctor\Resources\DoctorResource;
use Filament\Http\Middleware\Authenticate;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Filament\Navigation\MenuItem;
use Filament\Pages;
use Filament\Panel;
use Filament\PanelProvider;
use Filament\Support\Colors\Color;
use Filament\Widgets;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Session\Middleware\AuthenticateSession;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\View\Middleware\ShareErrorsFromSession;
use Saade\FilamentFullCalendar\FilamentFullCalendarPlugin;

class DoctorPanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        return $panel
            ->id('doctor')
            ->path('doctor')
            ->colors([
                'primary' => Color::Blue,
            ])
            ->login()
            ->emailVerification()
            // ->viteTheme("resources/css/app.css")
            ->viteTheme('resources/css/filament/doctor/theme.css')
            ->registration(Register::class)->emailVerification()
            ->profile()
            ->brandLogo(fn () => view('vendor.filament.components.brand'))
            ->userMenuItems([
                'profile' => MenuItem::make()->url(fn (): string => EditProfile::getUrl())
                    ->label(fn () => __('doctor/profile.edit-profile-title'))
                    ->color('info'),
            ])
            ->discoverResources(in: app_path('Filament/Doctor/Resources'), for: 'App\\Filament\\Doctor\\Resources')
            ->discoverPages(in: app_path('Filament/Doctor/Pages'), for: 'App\\Filament\\Doctor\\Pages')
            ->pages([
                Pages\Dashboard::class,
            ])
            ->discoverWidgets(in: app_path('Filament/Doctor/Widgets'), for: 'App\\Filament\\Doctor\\Widgets')
            ->widgets([
                // Widgets\AccountWidget::class,
                DoctorResource\Widgets\DoctorOverview::class,
                AppointmentResource\Widgets\AppointmentBar::class,
                AppointmentResource\Widgets\AppointmentLine::class,
                AppointmentResource\Widgets\AppointmentLine::class,
                AppointmentResource\Widgets\AppointmentsPerMonthBarChart::class,

                // Widgets\FilamentInfoWidget::class,
            ])
            ->middleware([
                EncryptCookies::class,
                AddQueuedCookiesToResponse::class,
                StartSession::class,
                AuthenticateSession::class,
                ShareErrorsFromSession::class,
                VerifyCsrfToken::class,
                SubstituteBindings::class,
                DisableBladeIconComponents::class,
                DispatchServingFilamentEvent::class,
            ])
            ->authMiddleware([
                Authenticate::class,
            ])

            ->plugins([
                FilamentFullCalendarPlugin::make()
                    // ->schedulerLicenseKey()
                    ->selectable()
                    ->editable(false),
                // ->timezone(config('app.timezone'))
                // ->plugins()
                // ->config()
            ])
            ->databaseNotifications()
            ->databaseNotificationsPolling('30s');
    }
}
