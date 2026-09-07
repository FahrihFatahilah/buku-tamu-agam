<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Template extends Model
{
    use HasFactory;

    protected $fillable = [
        'key', 'name', 'description', 'thumbnail', 'category',
        'is_active', 'default_settings', 'default_sections',
        'animation_personality', 'sort_order',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'default_settings' => 'array',
        'default_sections' => 'array',
        'animation_personality' => 'array',
    ];

    public function weddings()
    {
        return $this->hasMany(Wedding::class);
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true)->orderBy('sort_order');
    }
}
