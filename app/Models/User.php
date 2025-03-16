<?php

namespace App\Models;

use App\Enums\LangEnum;
use App\Enums\User\UserRoleEnum;
use App\Enums\User\UserSexEnum;
use App\Enums\User\UserStatusEnum;
use Filament\Models\Contracts\FilamentUser;
use Filament\Models\Contracts\HasAvatar;
use Filament\Models\Contracts\HasName;
use Filament\Panel;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Storage;
use Laravel\Sanctum\HasApiTokens;

/**
 * App\Model\User
 *
 * @property int $id
 * @property string|null $first_name
 * @property string $fullname
 * @property string $name
 * @property string|null $email
 * @property string|null $phone
 * @property string|null $avatar
 * @property UserRoleEnum::string $role
 * @property UserSexEnum::string $sex
 * @property UserStatusEnum::string $status
 * @property LangEnum::string $default_lang
 * @property string|null $email_verified_at
 * @property string|null $phone_verified_at
 * @property string $password
 * @property string $remember_token
 * @property string|null $one_signal_id
 * @property string $created_at
 * @property string $updated_at
 *
 * @property Patient $patient
 * @property Doctor $doctor
 */
class User extends Authenticatable implements FilamentUser, HasAvatar, HasName, MustVerifyEmail
{
    use HasApiTokens, HasFactory, Notifiable;

    public function canAccessPanel(Panel $panel): bool
    {
        if ($panel->getId() === 'admin') {
            return $this->role === UserRoleEnum::ADMIN && $this->hasVerifiedEmail();
        } elseif ($panel->getId() === 'doctor') {
            return $this->role === UserRoleEnum::DOCTOR && $this->hasVerifiedEmail();
        }

        return false;
    }

    public function getFilamentAvatarUrl(): string
    {
        return Storage::url($this->avatar ?? 'public/users/images/avatar.png');
    }

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'status',
        'role',
        'sex',
        'one_signal_id',
        'phone_verified_at',
        'avatar',
        'phone',
        'first_name',
        'sex',
        'default_lang',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'phone_verified_at' => 'datetime',
            'password' => 'hashed',
            'status' => UserStatusEnum::class,
            'role' => UserRoleEnum::class,
            'sex' => UserSexEnum::class,
            'default_lang' => LangEnum::class,
        ];
    }

    /**
     * doctor
     *
     * @return HasOne<Doctor>
     */
    public function doctor(): HasOne
    {
        return $this->hasOne(Doctor::class);
    }

    /**
     * patient
     *
     * @return HasOne<Patient>
     */
    public function patient(): HasOne
    {
        return $this->hasOne(Patient::class);
    }

    /**
     * getFullnameAttribute
     *
     * @return string
     */
    public function getFullnameAttribute(): string
    {
        $first_name = $this->first_name ? $this->first_name . ' ' : '';

        return $first_name . $this->name;
    }

    /**
     * getFilamentName
     *
     * @return string
     */
    public function getFilamentName(): string
    {
        return $this->fullname;
    }

    /**
     * isAdmin
     *
     * @return bool
     */
    public function isAdmin(): bool
    {
        return $this->role === UserRoleEnum::ADMIN;
    }
}
