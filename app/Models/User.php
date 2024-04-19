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
        'default_lang'
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

    public function doctor(): HasOne
    {
        return $this->hasOne(Doctor::class);
    }

    public function patient(): HasOne
    {
        return $this->hasOne(Patient::class);
    }

    public function getFullnameAttribute(): string
    {
        $first_name = $this->first_name ? $this->first_name . ' ' : '';

        return $first_name . $this->name;
    }

    public function getFilamentName(): string
    {
        return $this->fullname;
    }

    public function isAdmin(): bool
    {
        return $this->role === UserRoleEnum::ADMIN;
    }
}
