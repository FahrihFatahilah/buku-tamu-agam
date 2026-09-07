<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AuditLog extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'user_id', 'wedding_id', 'action', 'entity_type',
        'entity_id', 'metadata', 'ip_address', 'user_agent', 'created_at',
    ];

    protected $casts = [
        'metadata' => 'array',
        'created_at' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function wedding()
    {
        return $this->belongsTo(Wedding::class);
    }
}
