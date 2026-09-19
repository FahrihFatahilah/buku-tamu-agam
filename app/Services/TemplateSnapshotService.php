<?php

namespace App\Services;

use App\Models\Template;
use App\Models\TemplateSnapshot;
use App\Models\TemplateVersion;
use App\Models\Wedding;

class TemplateSnapshotService
{
    /**
     * Create an immutable snapshot of the latest published template version
     * and attach it to the wedding. Called when a wedding is created or when
     * the client selects a template.
     *
     * Returns null if the template has no published version yet.
     */
    public function createForWedding(Wedding $wedding, Template $template): ?TemplateSnapshot
    {
        $version = $template->versions()
            ->where('status', 'published')
            ->orderByDesc('version')
            ->first();

        if (! $version) {
            // Template has no published version — fall back to template's current state
            // by creating a synthetic snapshot from the template itself.
            return $this->createFromTemplate($wedding, $template);
        }

        return $this->createFromVersion($wedding, $version);
    }

    /**
     * Create snapshot from a specific published version.
     */
    public function createFromVersion(Wedding $wedding, TemplateVersion $version): TemplateSnapshot
    {
        // Remove any existing snapshot for this wedding
        TemplateSnapshot::where('wedding_id', $wedding->id)->delete();

        $snapshot = TemplateSnapshot::create([
            'wedding_id'            => $wedding->id,
            'template_id'           => $version->template_id,
            'template_version_id'   => $version->id,
            'version_number'        => $version->version,
            'document'              => $version->document,
            'default_settings'      => $version->default_settings,
            'default_sections'      => $version->default_sections,
            'animation_personality' => $version->animation_personality,
        ]);

        $wedding->update(['template_snapshot_id' => $snapshot->id]);

        return $snapshot;
    }

    /**
     * Fallback: create snapshot directly from template state (no published version).
     * Used for legacy templates or templates still in draft.
     */
    private function createFromTemplate(Wedding $wedding, Template $template): TemplateSnapshot
    {
        TemplateSnapshot::where('wedding_id', $wedding->id)->delete();

        // Use a synthetic version_id = 0 sentinel — we create a draft version first
        $draftVersion = $template->versions()->where('status', 'draft')->first()
            ?? TemplateVersion::create([
                'template_id'           => $template->id,
                'version'               => 1,
                'status'                => 'draft',
                'document'              => $template->builder_document,
                'default_settings'      => $template->default_settings,
                'default_sections'      => $template->default_sections,
                'animation_personality' => $template->animation_personality,
            ]);

        $snapshot = TemplateSnapshot::create([
            'wedding_id'            => $wedding->id,
            'template_id'           => $template->id,
            'template_version_id'   => $draftVersion->id,
            'version_number'        => $draftVersion->version,
            'document'              => $template->builder_document,
            'default_settings'      => $template->default_settings,
            'default_sections'      => $template->default_sections,
            'animation_personality' => $template->animation_personality,
        ]);

        $wedding->update(['template_snapshot_id' => $snapshot->id]);

        return $snapshot;
    }

    /**
     * Migrate a wedding's snapshot to a newer published version.
     * Admin-only, requires explicit confirmation.
     * Returns the new snapshot.
     */
    public function migrateToVersion(Wedding $wedding, TemplateVersion $newVersion): TemplateSnapshot
    {
        // Keep old snapshot as audit trail — don't delete it
        $old = $wedding->templateSnapshot;

        $snapshot = TemplateSnapshot::create([
            'wedding_id'            => $wedding->id,
            'template_id'           => $newVersion->template_id,
            'template_version_id'   => $newVersion->id,
            'version_number'        => $newVersion->version,
            'document'              => $newVersion->document,
            'default_settings'      => $newVersion->default_settings,
            'default_sections'      => $newVersion->default_sections,
            'animation_personality' => $newVersion->animation_personality,
        ]);

        $wedding->update(['template_snapshot_id' => $snapshot->id]);

        return $snapshot;
    }

    /**
     * Resolve the effective builder document for a wedding.
     * Priority: wedding's own builder_document > snapshot document > template document.
     */
    public function resolveDocument(Wedding $wedding): ?array
    {
        // Wedding has its own customized document
        if ($wedding->hasBuilderDocument()) {
            return $wedding->builder_document;
        }

        // Use snapshot document
        $snapshot = $wedding->templateSnapshot;
        if ($snapshot && ! empty($snapshot->document['nodes'])) {
            return $snapshot->document;
        }

        // Fall back to template's current document
        return $wedding->template?->builder_document;
    }
}
