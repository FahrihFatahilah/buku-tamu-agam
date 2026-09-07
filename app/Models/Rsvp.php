<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Rsvp extends Model
{
    protected $fillable = ['wedding_id', 'guest_id', 'attendance_status', 'pax', 'note', 'ip_address'];

    public function guest()
    {
        return $this->belongsTo(Guest::class);
    }

    public function wedding()
    {
        return $this->belongsTo(Wedding::class);
    }
}
