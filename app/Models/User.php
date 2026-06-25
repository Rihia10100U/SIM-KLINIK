<?php

namespace App\Models;

use App\Enums\Role;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
        'role' => Role::class,
    ];

    /**
     * Cek apakah user punya salah satu dari role yang diberikan.
     * Contoh: $user->hasRole('admin', 'resepsionis')
     */
    public function hasRole(string ...$roles): bool
    {
        return in_array($this->role?->value, $roles, true);
    }

    public function isAdmin(): bool
    {
        return $this->role === Role::Admin;
    }
}
