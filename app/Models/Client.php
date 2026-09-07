<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Client extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = ['name', 'email', 'phone', 'company', 'status', 'settings'];

    protected $casts = ['settings' => 'array'];

    public function users()
    {
        return $this->hasMany(User::class);
    }

    public function weddings()
    {
        return $this->hasMany(Wedding::class);
    }

    public function deployments()
    {
        return $this->hasMany(Deployment::class);
    }

    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }
}
