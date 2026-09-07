<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class WeddingEvent extends Model
{
    protected $fillable = [
        'wedding_id', 'name', 'type', 'starts_at', 'ends_at',
        'venue', 'address', 'latitude', 'longitude', 'maps_url', 'maps_embed',
        'dress_code', 'notes', 'is_public', 'sort_order',
    ];

    protected $casts = [
        'starts_at' => 'datetime',
        'ends_at' => 'datetime',
        'is_public' => 'boolean',
        'latitude' => 'decimal:7',
        'longitude' => 'decimal:7',
    ];

    public function wedding()
    {
        return $this->belongsTo(Wedding::class);
    }
}
