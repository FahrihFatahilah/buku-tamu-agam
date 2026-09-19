<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TemplateSnapshot extends Model
{
    protected $fillable = [
        'wedding_id', 'template_id', 'template_version_id', 'version_number',
        'document', 'default_settings', 'default_sections', 'animation_personality',
    ];

    protected $casts = [
        'document'             => 'array',
        'default_settings'     => 'array',
        'default_sections'     => 'array',
        'animation_personality'=> 'array',
    ];

    public function wedding()
    {
        return $this->belongsTo(Wedding::class);
    }

    public function template()
    {
        return $this->belongsTo(Template::class);
    }

    public function templateVersion()
    {
        return $this->belongsTo(TemplateVersion::class, 'template_version_id');
    }
}
