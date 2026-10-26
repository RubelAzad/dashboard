<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, HasRoles, Notifiable;

    public const SUPER_ADMIN_ROLE = 'Super Admin';

    public function isSuperAdmin(): bool
    {
        return $this->hasRole(self::SUPER_ADMIN_ROLE);
    }

    /**
     * True when this user is the only active Super Admin left.
     */
    public function isLastActiveSuperAdmin(): bool
    {
        return $this->is_active
            && $this->isSuperAdmin()
            && static::role(self::SUPER_ADMIN_ROLE)->where('is_active', true)->count() === 1;
    }

    /**
     * Values compared by the audit log when this user is created, edited or deleted.
     *
     * @return array<string, mixed>
     */
    public function auditSnapshot(): array
    {
        return [
            'name' => $this->name,
            'email' => $this->email,
            'is_active' => (bool) $this->is_active,
            'roles' => $this->getRoleNames()->sort()->values()->all(),
        ];
    }

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'is_active',
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
            'is_active' => 'boolean',
            'preferences' => 'array',
        ];
    }
}
