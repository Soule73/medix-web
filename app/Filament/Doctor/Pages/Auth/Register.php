<?php

namespace App\Filament\Doctor\Pages\Auth;

use App\Enums\User\UserRoleEnum;
use DanHarrin\LivewireRateLimiting\Exceptions\TooManyRequestsException;
use Filament\Http\Responses\Auth\Contracts\RegistrationResponse;
use Filament\Notifications\Notification;
use Filament\Pages\Auth\Register as BaseRegister;
use Illuminate\Auth\Events\Registered;

class Register extends BaseRegister
{
    public function register(): ?RegistrationResponse
    {
        try {
            $this->rateLimit(2);
        } catch (TooManyRequestsException $exception) {
            Notification::make()
                ->title(__('filament-panels::pages/auth/register.notifications.throttled.title', [
                    'seconds' => $exception->secondsUntilAvailable,
                    'minutes' => ceil($exception->secondsUntilAvailable / 60),
                ]))
                ->body(array_key_exists('body', __('filament-panels::pages/auth/register.notifications.throttled') ?: []) ? __('filament-panels::pages/auth/register.notifications.throttled.body', [
                    'seconds' => $exception->secondsUntilAvailable,
                    'minutes' => ceil($exception->secondsUntilAvailable / 60),
                ]) : null)
                ->danger()
                ->send();

            return null;
        }

        $user = $this->wrapInDatabaseTransaction(function () {
            $data = $this->form->getState();
            $data['role'] = UserRoleEnum::DOCTOR->value;

            return $this->handleRegistration($data);
        });

        event(new Registered($user));

        $this->sendEmailVerificationNotification($user);

        // Filament::auth()->login($user);

        Notification::make()
            ->title('Lien de vérification envoyé')
            ->body('Un lien de vérification à éte envoyé à votre addresse email pour pouvoir activer votre compte')
            ->persistent()
            ->success()
            ->send();
        session()->regenerate();

        return app(RegistrationResponse::class);
    }
}
