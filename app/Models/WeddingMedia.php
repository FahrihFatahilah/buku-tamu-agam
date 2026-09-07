<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class WeddingMedia extends Model
{
    protected $fillable = [
        'wedding_id', 'collection', 'file_path', 'original_name',
        'mime_type', 'file_size', 'width', 'height',
        'alt_text', 'sort_order', 'metadata',
    ];

    protected $casts = ['metadata' => 'array'];

    public function wedding()
    {
        return $this->belongsTo(Wedding::class);
    }

    public function url(): string
    {
        return \Storage::url($this->file_path);
    }
}
