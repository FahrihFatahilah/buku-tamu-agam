<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class WeddingSection extends Model
{
    protected $fillable = ['wedding_id', 'section_key', 'title', 'is_enabled', 'sort_order', 'settings'];

    protected $casts = [
        'is_enabled' => 'boolean',
        'settings' => 'array',
    ];

    public function wedding()
    {
        return $this->belongsTo(Wedding::class);
    }
}
