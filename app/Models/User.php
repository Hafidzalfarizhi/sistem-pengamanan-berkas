<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;

class User extends Authenticatable
{
    use HasFactory;

    public const ROLE_ADMIN = 'administrator';
    public const ROLE_USER = 'user';
    public const STATUS_ACTIVE = 'active';
    public const STATUS_INACTIVE = 'inactive';

    protected $fillable = ['name', 'username', 'password', 'role', 'status'];

    protected $hidden = ['password'];

    protected function casts(): array
    {
        return ['password' => 'hashed'];
    }

    // Tabel users tidak memiliki kolom remember_token.
    public function getRememberTokenName(): string
    {
        return '';
    }

    public function isAdministrator(): bool
    {
        return $this->role === self::ROLE_ADMIN;
    }

    public function isActive(): bool
    {
        return $this->status === self::STATUS_ACTIVE;
    }

    /** Awalan route sesuai role: "admin" atau "user". */
    public function routePrefix(): string
    {
        return $this->isAdministrator() ? 'admin' : 'user';
    }

    /** Nama route sesuai role, contoh: routeName('files.index') => admin.files.index */
    public function routeName(string $name): string
    {
        return $this->routePrefix() . '.' . $name;
    }

    public function getRoleLabelAttribute(): string
    {
        return $this->isAdministrator() ? 'Administrator' : 'User';
    }

    public function scopeSearch(Builder $query, ?string $term): Builder
    {
        if ($term === null || trim($term) === '') {
            return $query;
        }

        $like = '%' . addcslashes(trim($term), '%_\\') . '%';

        return $query->where(fn (Builder $q) => $q->where('name', 'like', $like)->orWhere('username', 'like', $like));
    }

    public function files(): HasMany
    {
        return $this->hasMany(File::class);
    }

    public function activityLogs(): HasMany
    {
        return $this->hasMany(ActivityLog::class);
    }

    public function notifications(): HasMany
    {
        return $this->hasMany(Notification::class);
    }
}
