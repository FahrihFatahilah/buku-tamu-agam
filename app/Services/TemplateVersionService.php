<?php

namespace App\Services;

use App\Models\Template;
use App\Models\TemplateVersion;
use App\Models\User;

class TemplateVersionService
{
    public function __construct(private AuditLogService $audit) {}

    /**
     * Create a new draft version from the template's current state.
     * If a draft already exists, return it (only one draft at a time).
     */
    public function createDraft(Template $template, ?string $changelog = null): TemplateVersion
    {
        $existing = $template->versions()->where('status', 'draft')->first();
        if ($existing) {
            return $existing;
        }

        $nextVersion = ($template->versions()->max('version') ?? 0) + 1;

        $version = TemplateVersion::create([
            'template_id'           => $template->id,
            'version'               => $nextVersion,
            'status'                => 'draft',
            'document'              => $template->builder_document,
            'default_settings'      => $template->default_settings,
            'default_sections'      => $template->default_sections,
            'animation_personality' => $template->animation_personality,
            'changelog'             => $changelog,
        ]);

        $this->audit->log('template.version_created', 'template_version', $version->id, [
            'template' => $template->key,
            'version'  => $nextVersion,
        ]);

        return $version;
    }

    /**
     * Sync the draft version with the template's current builder document.
     * Called whenever the template is saved in the builder.
     */
    public function syncDraft(Template $template): ?TemplateVersion
    {
        $draft = $template->versions()->where('status', 'draft')->first();
        if (! $draft) {
            return null;
        }

        $draft->update([
            'document'              => $template->builder_document,
            'default_settings'      => $template->default_settings,
            'default_sections'      => $template->default_sections,
            'animation_personality' => $template->animation_personality,
        ]);

        return $draft;
    }

    /**
     * Publish a draft version. Makes it immutable and updates the template status.
     */
    public function publish(TemplateVersion $version, User $user): TemplateVersion
    {
        if (! $version->isDraft()) {
            return $version;
        }

        $version->update([
            'status'       => 'published',
            'published_at' => now(),
            'published_by' => $user->id,
        ]);

        // Update template's current_version counter and status
        $version->template->update([
            'status'          => 'published',
            'current_version' => $version->version,
            'is_active'       => true,
        ]);

        $this->audit->log('template.version_published', 'template_version', $version->id, [
            'template' => $version->template->key,
            'version'  => $version->version,
        ]);

        return $version->fresh();
    }

    /**
     * Archive a published version.
     */
    public function archive(TemplateVersion $version): TemplateVersion
    {
        $version->update(['status' => 'archived']);

        $this->audit->log('template.version_archived', 'template_version', $version->id, [
            'template' => $version->template->key,
            'version'  => $version->version,
        ]);

        return $version;
    }

    /**
     * Get the latest published version for a template.
     */
    public function latestPublished(Template $template): ?TemplateVersion
    {
        return $template->versions()->where('status', 'published')->orderByDesc('version')->first();
    }

    /**
     * Duplicate a template — creates a new Template with a new draft version
     * copied from the source template's latest published version (or current state).
     */
    public function duplicateTemplate(Template $source, string $newName, User $user): Template
    {
        $newKey = \Illuminate\Support\Str::slug($newName);
        $i = 2;
        while (Template::where('key', $newKey)->exists()) {
            $newKey = \Illuminate\Support\Str::slug($newName) . '-' . $i++;
        }

        $copy = Template::create([
            'key'                   => $newKey,
            'name'                  => $newName,
            'description'           => $source->description,
            'category'              => $source->category,
            'is_active'             => false,
            'status'                => 'draft',
            'current_version'       => 0,
            'default_settings'      => $source->default_settings,
            'default_sections'      => $source->default_sections,
            'animation_personality' => $source->animation_personality,
            'builder_document'      => $source->builder_document,
            'sort_order'            => Template::max('sort_order') + 1,
        ]);

        // Create initial draft version
        $this->createDraft($copy, "Duplikat dari {$source->name}");

        $this->audit->log('template.duplicated', 'template', $copy->id, [
            'source' => $source->key,
            'new'    => $copy->key,
        ]);

        return $copy;
    }
}
