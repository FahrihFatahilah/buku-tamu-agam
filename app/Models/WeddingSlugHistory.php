<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class WeddingSlugHistory extends Model
{
    public $timestamps = false;
    protected $table = 'wedding_slug_history';

    protected $fillable = ['wedding_id', 'slug', 'retired_at'];

    protected $casts = ['retired_at' => 'datetime'];

    public function wedding()
    {
        return $this->belongsTo(Wedding::class);
    }
}
