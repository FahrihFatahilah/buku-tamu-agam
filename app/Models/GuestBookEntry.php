<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class GuestBookEntry extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'wedding_id', 'guest_id', 'name', 'message',
        'attendance_status', 'pax', 'status', 'ip_address',
    ];

    public function wedding()
    {
        return $this->belongsTo(Wedding::class);
    }

    public function guest()
    {
        return $this->belongsTo(Guest::class);
    }

    public function scopeApproved($query)
    {
        return $query->where('status', 'approved');
    }

    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }
}
