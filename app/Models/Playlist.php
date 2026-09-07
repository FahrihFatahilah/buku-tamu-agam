<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Playlist extends Model
{
    protected $fillable = [
        'wedding_id', 'name', 'is_active', 'autoplay', 'loop', 'shuffle', 'volume',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'autoplay' => 'boolean',
        'loop' => 'boolean',
        'shuffle' => 'boolean',
    ];

    public function wedding()
    {
        return $this->belongsTo(Wedding::class);
    }

    public function items()
    {
        return $this->hasMany(PlaylistItem::class)->orderBy('sort_order');
    }
}
