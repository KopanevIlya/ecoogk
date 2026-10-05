<?php

namespace App\Models;

use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
        'site_id',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    public function reports()
    {
        return $this->hasMany(\App\Models\Report::class);
    }

    public function isAdmin(): bool
    {
        return $this->role === 'admin';
    }

    public function isEcologist(): bool
    {
        return $this->role === 'ecologist';
    }

    public function isResponsible(): bool
    {
        return $this->role === 'responsible';
    }

    public function canSeeAiResults(): bool
    {
        return $this->isAdmin() || $this->isEcologist();
    }

    public function canManageAllReports(): bool
    {
        return $this->isAdmin() || $this->isEcologist();
    }
}