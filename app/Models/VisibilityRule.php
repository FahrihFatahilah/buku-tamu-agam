<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class VisibilityRule extends Model
{
    protected $fillable = [
        'wedding_id', 'entity_type', 'entity_id',
        'scope', 'scope_id', 'is_visible',
    ];

    protected $casts = ['is_visible' => 'boolean'];

    public function wedding()
    {
        return $this->belongsTo(Wedding::class);
    }
}
