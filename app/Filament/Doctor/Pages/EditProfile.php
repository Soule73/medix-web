<?php

namespace App\Filament\Doctor\Pages;

use Exception;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Pages\Page;
use Filament\Actions\Action;
use Filament\Facades\Filament;
use App\Enums\User\UserSexEnum;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Database\Eloquent\Model;
use Filament\Notifications\Notification;
use Illuminate\Validation\Rules\Password;
use Illuminate\Contracts\Auth\Authenticatable;
use Filament\Forms\Concerns\InteractsWithForms;

class EditProfile extends Page
{
    use InteractsWithForms;

    protected static ?string $navigationIcon = 'heroicon-o-document-text';

    protected static string $view = 'filament.doctor.pages.edit-profile';

    protected static bool $shouldRegisterNavigation = false;

    public ?array $profileData = [];
    public ?array $passwordData = [];

    /**
     * On définit ici les clés des formulaires utilisés dans la page.
     */
    protected function getForms(): array
    {
        return [
            'editProfileForm',
            'editPasswordForm',
        ];
    }

    /**
     * Configuration du formulaire d'édition du profil.
     */
    public function editProfileForm(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make(__('doctor/profile.edit-profile-form-section-title'))
                    ->description(__('doctor/profile.edit-profile-form-section-description'))
                    ->aside()
                    ->schema([
                        Forms\Components\FileUpload::make('avatar')
                            ->label(__('doctor/profile.edit-profile-form-avatar'))
                            ->avatar()
                            ->disk('local')
                            ->directory('public/users/images')
                            ->visibility('public'),
                        Forms\Components\TextInput::make('first_name')
                            ->label(__('doctor/profile.edit-profile-form-firs-name')),
                        Forms\Components\TextInput::make('name')
                            ->label(__('doctor/profile.edit-profile-form-last-name'))
                            ->required(),
                        Forms\Components\TextInput::make('email')
                            ->label('E-mail')
                            ->unique('users', 'email', Auth::user())
                            ->email()
                            ->required()
                            ->unique(ignoreRecord: true),
                        Forms\Components\TextInput::make('phone')
                            ->label(__('doctor/profile.edit-profile-form-phone')),
                        Forms\Components\Select::make('sex')
                            ->required()
                            ->searchable()
                            ->options([
                                UserSexEnum::MAN->value   => __('doctor/doctor.user-sex-man'),
                                UserSexEnum::WOMAN->value => __('doctor/doctor.user-sex-woman'),
                            ])
                            ->label(__('doctor/doctor.user-sex')),
                    ])->collapsed(),
            ])
            ->model($this->getUser())
            ->statePath('profileData');
    }

    /**
     * Configuration du formulaire de modification du mot de passe.
     */
    public function editPasswordForm(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make(__('doctor/profile.edit-password-form-section-title'))
                    ->description(__('doctor/profile.edit-profile-form-section-description'))
                    ->aside()
                    ->schema([
                        Forms\Components\TextInput::make('currentPassword')
                            ->label(__('doctor/profile.edit-password-form-current-password'))
                            ->password()
                            ->required()
                            ->currentPassword(),
                        Forms\Components\TextInput::make('password')
                            ->label(__('doctor/profile.edit-password-form-new-password'))
                            ->password()
                            ->required()
                            ->rule(Password::default())
                            ->autocomplete('new-password')
                            ->dehydrateStateUsing(fn($state): string => Hash::make($state))
                            ->live(debounce: 500)
                            ->same('passwordConfirmation'),
                        Forms\Components\TextInput::make('passwordConfirmation')
                            ->label(__('doctor/profile.edit-password-form-confirm-password'))
                            ->password()
                            ->required()
                            ->dehydrated(false),
                    ]),
            ])
            ->model($this->getUser())
            ->statePath('passwordData');
    }

    /**
     * Récupère l'utilisateur courant sous forme de modèle.
     */
    protected function getUser(): Authenticatable & Model
    {
        $user = Filament::auth()->user();
        if (! $user instanceof Model) {
            throw new Exception(__('doctor/profile.get-user-exception'));
        }

        return $user;
    }

    /**
     * Méthode de cycle de vie.
     * Pensez à appeler initializeForms() ici si vos formulaires ne sont pas encore initialisés automatiquement.
     */
    public function mount(): void
    {
        // Si besoin, décommentez la ligne suivante selon votre version de Filament :
        // $this->initializeForms();
        $this->fillForms();
    }

    /**
     * Remplit les formulaires à partir des données de l'utilisateur authentifié.
     */
    protected function fillForms(): void
    {
        $data = $this->getUser()->attributesToArray();
        // Utilisation de getForm() pour récupérer une instance de formulaire par sa clé.
        $this->getForm('editProfileForm')->fill($data);
        $this->getForm('editPasswordForm')->fill();
    }

    protected function getUpdateProfileFormActions(): array
    {
        return [
            Action::make('updateProfileAction')
                ->label(__('filament-panels::pages/auth/edit-profile.form.actions.save.label'))
                ->submit('editProfileForm'),
        ];
    }

    protected function getUpdatePasswordFormActions(): array
    {
        return [
            Action::make('updatePasswordAction')
                ->label(__('filament-panels::pages/auth/edit-profile.form.actions.save.label'))
                ->submit('editPasswordForm'),
        ];
    }

    /**
     * Met à jour le profil de l'utilisateur.
     */
    public function updateProfile(): void
    {
        $data = $this->getForm('editProfileForm')->getState();
        $this->handleRecordUpdate($this->getUser(), $data);
        $this->sendSuccessNotification();
    }

    /**
     * Met à jour le mot de passe de l'utilisateur.
     */
    public function updatePassword(): void
    {
        $data = $this->getForm('editPasswordForm')->getState();
        $this->handleRecordUpdate($this->getUser(), $data);
        if (request()->hasSession() && array_key_exists('password', $data)) {
            request()->session()->put([
                'password_hash_' . Filament::getAuthGuard() => $data['password'],
            ]);
        }
        $this->getForm('editPasswordForm')->fill();
        $this->sendSuccessNotification();
    }

    /**
     * Met à jour le modèle utilisateur avec les données du formulaire.
     */
    private function handleRecordUpdate(Model $record, array $data): Model
    {
        $record->update($data);
        return $record;
    }

    /**
     * Envoie une notification de succès.
     */
    private function sendSuccessNotification(): void
    {
        Notification::make()
            ->success()
            ->title(__('filament-panels::pages/auth/edit-profile.notifications.saved.title'))
            ->send();
    }
}
