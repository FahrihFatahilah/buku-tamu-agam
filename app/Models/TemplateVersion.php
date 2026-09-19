<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TemplateVersion extends Model
{
    protected $fillable = [
        'template_id', 'version', 'status', 'document',
        'default_settings', 'default_sections', 'animation_personality',
        'changelog', 'published_at', 'published_by',
    ];

    protected $casts = [
        'document'             => 'array',
        'default_settings'     => 'array',
        'default_sections'     => 'array',
        'animation_personality'=> 'array',
        'published_at'         => 'datetime',
    ];

    public function template()
    {
        return $this->belongsTo(Template::class);
    }

    public function publishedBy()
    {
        return $this->belongsTo(User::class, 'published_by');
    }

    public function snapshots()
    {
        return $this->hasMany(TemplateSnapshot::class);
    }

    public function isPublished(): bool
    {
        return $this->status === 'published';
    }

    public function isDraft(): bool
    {
        return $this->status === 'draft';
    }

    public function scopePublished($query)
    {
        return $query->where('status', 'published');
    }
}
