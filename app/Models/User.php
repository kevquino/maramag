<?php
// app/Models/User.php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Fortify\TwoFactorAuthenticatable;

class User extends Authenticatable implements MustVerifyEmail
{
    use HasFactory, Notifiable, TwoFactorAuthenticatable;

    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
        'is_active',
        'last_login_at', // Add this
    ];

    protected $hidden = [
        'password',
        'remember_token',
        'two_factor_recovery_codes',
        'two_factor_secret',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'last_login_at' => 'datetime', // Add this
            'is_active' => 'boolean',
        ];
    }

    /**
     * Available roles
     */
    public static function getRoles(): array
    {
        return [
            'admin' => 'Administrator',
            'PIO Officer' => 'PIO Officer',
            'PIO Staff' => 'PIO Staff',
            'user' => 'Regular User',
        ];
    }

    /**
     * Check if user can manage news
     */
    public function canManageNews(): bool
    {
        return in_array($this->role, ['admin', 'PIO Officer', 'PIO Staff']);
    }

    /**
     * Check if user is admin
     */
    public function isAdmin(): bool
    {
        return $this->role === 'admin';
    }

    /**
     * Check if user is PIO Officer
     */
    public function isPioOfficer(): bool
    {
        return $this->role === 'PIO Officer';
    }

    /**
     * Check if user is PIO Staff
     */
    public function isPioStaff(): bool
    {
        return $this->role === 'PIO Staff';
    }

    /**
     * Check if user is active
     */
    public function isActive(): bool
    {
        return $this->is_active ?? true;
    }
}