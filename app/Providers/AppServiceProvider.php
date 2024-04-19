<?php

namespace App\Providers;

use App\Models\User;
use Filament\Facades\Filament;
use Laravel\Pulse\Facades\Pulse;
use Filament\View\PanelsRenderHook;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\ServiceProvider;
use Filament\Support\Facades\FilamentView;
use BezhanSalleh\FilamentLanguageSwitch\LanguageSwitch;
use Illuminate\Support\Facades\Storage;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        LanguageSwitch::configureUsing(function (LanguageSwitch $switch) {
            $switch
                ->locales(['ar', 'en', 'fr']);
        });

        Filament::serving(function () {

            FilamentView::registerRenderHook(
                PanelsRenderHook::HEAD_END,
                fn (): string => Blade::render('@laravelPWA'),
            );
            // FilamentView::registerRenderHook(
            //     PanelsRenderHook::HEAD_START,
            //     fn (): string => Blade::render("@vite('resources/css/app.css')"),
            // );
        });

        Gate::define('viewPulse', function (User $user) {
            return $user->isAdmin();
        });

        Pulse::user(fn ($user) => [
            'name' => $user->fullname,
            'extra' => $user->email,
            'avatar' => $user->avatar && Storage::fileExists($user->avatar) ? Storage::url($user->avatar) : $user->avatar,
        ]);
    }
}
