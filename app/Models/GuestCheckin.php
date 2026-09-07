<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class GuestCheckin extends Model
{
    protected $fillable = ['wedding_id', 'guest_id', 'checked_in_at', 'pax', 'checked_in_by', 'notes'];

    protected $casts = ['checked_in_at' => 'datetime'];

    public function guest()
    {
        return $this->belongsTo(Guest::class);
    }

    public function wedding()
    {
        return $this->belongsTo(Wedding::class);
    }

    public function operator()
    {
        return $this->belongsTo(User::class, 'checked_in_by');
    }
}
