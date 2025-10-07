<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Fortify\TwoFactorAuthenticatable;
use Illuminate\Database\Eloquent\Relations\HasMany;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable, TwoFactorAuthenticatable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'role', // Added role to fillable
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
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
            'password' => 'hashed',
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
     * Relationship with news articles
     */
    public function newsArticles()
    {
        return $this->hasMany(News::class, 'author_id');
    }

    /**
     * Relationship with activities
     */
    public function activities(): HasMany
    {
        return $this->hasMany(Activity::class);
    }

    /**
     * Log an activity for this user.
     */
    public function logActivity(string $description, string $type, array $metadata = []): Activity
    {
        return Activity::create([
            'description' => $description,
            'type' => $type,
            'metadata' => $metadata,
            'user_id' => $this->id,
        ]);
    }
}