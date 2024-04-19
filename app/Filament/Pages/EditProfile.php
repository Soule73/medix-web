<?php

namespace App\Filament\Pages;

use App\Enums\User\UserSexEnum;
use Exception;
use Filament\Actions\Action;
use Filament\Facades\Filament;
use Filament\Forms;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;

class EditProfile extends Page
{
    use InteractsWithForms;

    protected static ?string $navigationIcon = 'heroicon-o-document-text';

    protected static string $view = 'filament.doctor.pages.edit-profile';

    protected static bool $shouldRegisterNavigation = false;

    // protected static ?string $title = 'Modifier votre profile';

    // protected static ?string $navigationLabel = 'Modifier votre profile';

    public function getTitle(): string
    {
        return __('doctor/profile.edit-profile');
    }

    public static function getNavigationLabel(): string
    {
        return __('doctor/profile.edit-profile');
    }

    public ?array $profileData = [];

    public ?array $passwordData = [];

    public function mount(): void
    {
        $this->fillForms();
    }

    protected function getForms(): array
    {
        return [
            'editProfileForm',
            'editPasswordForm',
        ];
    }

    public function editProfileForm(Form $form): Form
    {
        return
            $form
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
                                ->visibility('public')
                                ->imageEditor(),
                            Forms\Components\TextInput::make('first_name')
                                ->label(__('doctor/profile.edit-profile-form-firs-name')),
                            Forms\Components\TextInput::make('name')
                                ->label(__('doctor/profile.edit-profile-form-last-name'))
                                ->required(),
                            Forms\Components\TextInput::make('email')->label('E-mail')
                                ->unique('users', 'email', auth()->user())
                                ->email()
                                ->required()
                                ->unique(ignoreRecord: true),
                            Forms\Components\TextInput::make('phone')
                                ->label(__('doctor/profile.edit-profile-form-phone')),
                            Forms\Components\Select::make('sex')
                                ->required()
                                ->searchable()
                                ->options([
                                    UserSexEnum::MAN->value => __('doctor/doctor.user-sex-man'),
                                    UserSexEnum::WOMAN->value => __('doctor/doctor.user-sex-woman'),
                                ])
                                ->label(__('doctor/doctor.user-sex')),
                        ]),
                ])
                ->model($this->getUser())
                ->statePath('profileData');
    }

    public function editPasswordForm(Form $form): Form
    {
        return
            $form
                ->schema([
                    Forms\Components\Section::make(__('doctor/profile.edit-password-form-section-title'))
                        ->description(__('doctor/profile.edit-profile-form-section-description'))
                        ->aside()

                        ->schema([
                            Forms\Components\TextInput::make('currentPassword')->label(__('doctor/profile.edit-password-form-current-password'))
                                ->password()
                                ->required()
                                ->currentPassword(),
                            Forms\Components\TextInput::make('password')->label(__('doctor/profile.edit-password-form-new-password'))
                                ->password()
                                ->required()
                                ->rule(Password::default())
                                ->autocomplete('new-password')
                                ->dehydrateStateUsing(fn ($state): string => Hash::make($state))
                                ->live(debounce: 500)
                                ->same('passwordConfirmation'),
                            Forms\Components\TextInput::make('passwordConfirmation')->label(__('doctor/profile.edit-password-form-confirm-password'))
                                ->password()
                                ->required()
                                ->dehydrated(false),
                        ]),
                ])
                ->model($this->getUser())
                ->statePath('passwordData');
    }

    protected function getUser(): Authenticatable&Model
    {
        $user = Filament::auth()->user();
        if (! $user instanceof Model) {
            throw new Exception(__('doctor/profile.get-user-exception'));
        }

        return $user;
    }

    protected function fillForms(): void
    {
        $data = $this->getUser()->attributesToArray();
        $this->editProfileForm->fill($data);
        $this->editPasswordForm->fill();
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

    public function updateProfile(): void
    {
        $data = $this->editProfileForm->getState();
        $this->handleRecordUpdate($this->getUser(), $data);
        $this->sendSuccessNotification();
    }

    public function updatePassword(): void
    {
        $data = $this->editPasswordForm->getState();
        $this->handleRecordUpdate($this->getUser(), $data);
        if (request()->hasSession() && array_key_exists('password', $data)) {
            request()->session()->put(['password_hash_'.Filament::getAuthGuard() => $data['password']]);
        }
        $this->editPasswordForm->fill();
        $this->sendSuccessNotification();
    }

    private function handleRecordUpdate(Model $record, array $data): Model
    {
        $record->update($data);

        return $record;
    }

    private function sendSuccessNotification(): void
    {
        Notification::make()
            ->success()
            ->title(__('filament-panels::pages/auth/edit-profile.notifications.saved.title'))
            ->send();
    }
}
