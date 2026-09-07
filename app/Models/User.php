<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    protected $fillable = ['name', 'email', 'password', 'client_id', 'role', 'is_active', 'last_login_at'];

    protected $hidden = ['password', 'remember_token'];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'last_login_at' => 'datetime',
        'is_active' => 'boolean',
        'password' => 'hashed',
    ];

    public function client()
    {
        return $this->belongsTo(Client::class);
    }

    public function isSuperAdmin(): bool
    {
        return $this->role === 'super_admin';
    }

    public function isClientAdmin(): bool
    {
        return $this->role === 'client_admin';
    }

    public function isCheckinOperator(): bool
    {
        return $this->role === 'checkin_operator';
    }

    public function canManageWedding(Wedding $wedding): bool
    {
        if ($this->isSuperAdmin()) {
            return true;
        }

        return $this->client_id === $wedding->client_id;
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
}
