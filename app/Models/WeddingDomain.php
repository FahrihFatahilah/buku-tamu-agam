<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class WeddingDomain extends Model
{
    protected $fillable = [
        'wedding_id', 'domain', 'type', 'is_primary', 'is_active',
        'verification_status', 'verification_token', 'verified_at', 'ssl_status',
    ];

    protected $casts = [
        'is_primary' => 'boolean',
        'is_active' => 'boolean',
        'verified_at' => 'datetime',
    ];

    public function wedding()
    {
        return $this->belongsTo(Wedding::class);
    }

    public function isVerified(): bool
    {
        return $this->verification_status === 'verified';
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
}
