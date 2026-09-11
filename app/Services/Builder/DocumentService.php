<?php

namespace App\Services\Builder;

use App\Builder\DocumentMigrator;
use App\Builder\DocumentValidator;
use App\Models\Template;
use App\Models\Wedding;
use App\Services\AuditLogService;
use Illuminate\Support\Str;

/**
 * All persistence rules for builder documents live here, so the controller
 * stays thin (matching the project's existing service-layer convention).
 */
class DocumentService
{
    public function __construct(
        private DocumentMigrator $migrator,
        private DocumentValidator $validator,
        private AuditLogService $audit,
    ) {}

    /**
     * The document to hand to the editor: the stored one, or a freshly
     * synthesised document built from the wedding's current sections and
     * template theme (nothing is written until the admin saves).
     */
    public function forEditor(Wedding $wedding): array
    {
        return $this->migrator->migrate($wedding->builder_document, $wedding);
    }

    /**
     * The document to render publicly.
     */
    public function forRender(Wedding $wedding): ?array
    {
        if (! $wedding->hasBuilderDocument()) {
            return null;
        }

        return $this->migrator->migrate($wedding->builder_document, $wedding);
    }

    /**
     * Validate and persist a document.
     *
     * @return array{document: array, errors: array}
     */
    public function save(Wedding $wedding, array $input): array
    {
        [$document, $errors] = $this->validator->validate($input, $wedding);

        // Even with warnings we persist the sanitised document — the client's
        // payload is never stored verbatim.
        $wedding->update(['builder_document' => $document]);

        $this->audit->log('wedding.builder_saved', 'wedding', $wedding->id, [
            'nodes' => count($document['nodes'] ?? []),
            'overlays' => count($document['overlays'] ?? []),
            'warnings' => count($errors),
        ], $wedding->id);

        return ['document' => $document, 'errors' => $errors];
    }

    /**
     * Opt in to the document renderer by materialising the synthesised
     * document. Reversible via revert().
     */
    public function enable(Wedding $wedding): array
    {
        $document = $this->migrator->migrate($wedding->builder_document, $wedding);

        $wedding->update(['builder_document' => $document]);

        $this->audit->log('wedding.builder_enabled', 'wedding', $wedding->id, [], $wedding->id);

        return $document;
    }

    /**
     * Return the wedding to its coded template.
     */
    public function revert(Wedding $wedding): void
    {
        $wedding->update(['builder_document' => null]);

        $this->audit->log('wedding.builder_reverted', 'wedding', $wedding->id, [], $wedding->id);
    }

    /**
     * Seed a wedding's document from a template's document, so templates act
     * as starting points without the invitation mutating the template.
     */
    public function seedFromTemplate(Wedding $wedding, ?Template $template = null): array
    {
        $template ??= $wedding->template;

        $source = $template?->builder_document;

        if (! $this->migrator->looksLikeDocument(is_array($source) ? $source : null)) {
            // Fall back to synthesising from the wedding's own sections.
            return $this->migrator->migrate(null, $wedding);
        }

        $document = $this->migrator->migrate($source, $wedding);

        $this->audit->log('wedding.builder_seeded', 'wedding', $wedding->id, [
            'template' => $template?->key,
        ], $wedding->id);

        return $document;
    }

    /**
     * Copy a wedding's document onto another wedding (duplicate invitation).
     */
    public function duplicate(Wedding $source, Wedding $target): array
    {
        $document = $this->migrator->migrate($source->builder_document, $source);

        // Fresh ids so the two documents never share node identity.
        $document['nodes'] = $this->rekeyNodes($document['nodes'] ?? []);
        $document['overlays'] = $this->rekeyOverlays($document['overlays'] ?? []);

        $target->update(['builder_document' => $document]);

        $this->audit->log('wedding.builder_duplicated', 'wedding', $target->id, [
            'from' => $source->id,
        ], $target->id);

        return $document;
    }

    private function rekeyNodes(array $nodes): array
    {
        $out = [];

        foreach ($nodes as $node) {
            if (! is_array($node)) {
                continue;
            }

            $node['id'] = 'n_'.Str::lower(Str::random(8));
            $node['children'] = $this->rekeyNodes($node['children'] ?? []);

            $out[] = $node;
        }

        return $out;
    }

    private function rekeyOverlays(array $overlays): array
    {
        $out = [];

        foreach ($overlays as $overlay) {
            if (! is_array($overlay)) {
                continue;
            }

            $overlay['id'] = 'ov_'.Str::lower(Str::random(8));

            $out[] = $overlay;
        }

        return $out;
    }
}
