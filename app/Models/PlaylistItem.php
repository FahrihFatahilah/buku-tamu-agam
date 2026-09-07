<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PlaylistItem extends Model
{
    protected $fillable = [
        'playlist_id', 'title', 'artist', 'file_path',
        'file_size', 'duration', 'start_position', 'sort_order',
    ];

    public function playlist()
    {
        return $this->belongsTo(Playlist::class);
    }

    public function url(): string
    {
        return \Storage::url($this->file_path);
    }
}
