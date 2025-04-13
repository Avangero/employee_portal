<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Filament\Models\Contracts\FilamentUser;
use Filament\Panel;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable implements FilamentUser
{
    use HasApiTokens, HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'first_name',
        'last_name',
        'email',
        'password',
        'position',
        'team_id',
        'manager_id',
        'role_id',
        'telegram_id',
        'is_reviewer',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = ['password', 'remember_token'];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
        'is_reviewer' => 'boolean',
        'has_telegram' => 'boolean',
    ];

    protected $appends = ['name', 'has_telegram'];

    public function canAccessPanel(Panel $panel): bool
    {
        return $this->isAdministrator();
    }

    public function getHomeRoute(): string
    {
        return '/admin';
    }

    public function getFilamentName(): string
    {
        return $this->getFullNameAttribute();
    }

    public function getAuthPasswordName(): string
    {
        return 'password';
    }

    public function team(): BelongsTo
    {
        return $this->belongsTo(Team::class);
    }

    public function manager(): BelongsTo
    {
        return $this->belongsTo(User::class, 'manager_id');
    }

    public function subordinates(): HasMany
    {
        return $this->hasMany(User::class, 'manager_id');
    }

    public function role(): BelongsTo
    {
        return $this->belongsTo(Role::class);
    }

    public function getFullNameAttribute(): string
    {
        return "{$this->first_name} {$this->last_name}";
    }

    public function isAdministrator(): bool
    {
        return $this->role?->slug === 'administrator';
    }

    public function isManager(): bool
    {
        return $this->role?->slug === 'manager';
    }

    public function getFilamentAvatar(): ?string
    {
        return null;
    }

    public function getNameAttribute(): string
    {
        return $this->getFullNameAttribute();
    }

    public function pullRequests(): HasMany
    {
        return $this->hasMany(PullRequest::class, 'author_id');
    }

    public function reviews(): HasMany
    {
        return $this->hasMany(PullRequestReview::class, 'reviewer_id');
    }

    public function isReviewer(): bool
    {
        return $this->is_reviewer;
    }

    public function getHasTelegramAttribute(): bool
    {
        return ! empty($this->telegram_id);
    }
}
