<?php

namespace App\Http\Controllers\Admin;

use App\Builder\DocumentMigrator;
use App\Builder\DocumentValidator;
use App\Builder\PageRenderer;
use App\Builder\Registry\AnimationRegistry;
use App\Builder\Registry\DecorationRegistry;
use App\Builder\Registry\EffectRegistry;
use App\Builder\Registry\OverlayRegistry;
use App\Builder\Registry\WidgetRegistry;
use App\Http\Controllers\Controller;
use App\Models\Template;
use App\Services\AuditLogService;
use App\Services\TemplatePreviewService;
use App\Services\TemplateService;
use App\Services\TemplateVersionService;
use Illuminate\Http\Request;

/**
 * Visual builder for master templates.
 *
 * Reuses the exact same builder shell, registry, and renderer as the wedding
 * builder — the only difference is the subject (Template vs Wedding) and the
 * preview endpoint, which renders against dummy data via TemplatePreviewService.
 */
class TemplateBuilderController extends Controller
{
    public function __construct(
        private DocumentMigrator $migrator,
        private DocumentValidator $validator,
        private TemplatePreviewService $previewService,
        private TemplateService $templateService,
        private TemplateVersionService $versionService,
        private AuditLogService $audit,
    ) {}

    /**
     * The builder shell for a master template.
     */
    public function edit(Template $template)
    {
        $this->authorize('update', $template);

        $document = $this->migrator->migrate($template->builder_document, $template);
        $isEmpty  = empty($document['nodes']);

        return view('builder.editor', [
            'context'     => 'template',
            'template'    => $template,
            'wedding'     => null,
            'document'    => $document,
            'registry'    => $this->registryPayload(),
            'giftMethods' => collect(),
            'previewUrl'  => route('admin.templates.builder.preview', $template),
            'stageUrl'    => route('admin.templates.builder.preview.stage', $template),
            'saveUrl'     => route('admin.templates.builder.document.update', $template),
            'enableUrl'   => null,
            'seedUrl'     => route('admin.templates.builder.seed', $template),
            'revertUrl'   => route('admin.templates.builder.document.destroy', $template),
            'publishUrl'  => route('admin.templates.builder.publish', $template),
            'isActive'    => ! $isEmpty,
            'isEmpty'     => $isEmpty,
            'usesCodedTemplate' => false,
            'templateKey' => $template->key,
            'backUrl'     => route('admin.templates.edit', $template),
        ]);
    }

    /**
     * Canvas preview — renders the template document against dummy wedding data.
     */
    public function preview(Request $request, Template $template)
    {
        $this->authorize('update', $template);

        $document = $request->session()->get("tpl_builder_preview_{$template->id}");

        if (! is_array($document)) {
            $document = $this->migrator->migrate($template->builder_document, $template);
        }

        // Build dummy view data (same as template preview, but with builder document)
        $palette  = $template->default_settings['palette'] ?? [];
        $fonts    = $template->default_settings['fonts'] ?? [];

        $paletteKey  = array_search($palette, config('ngundang.palettes', [])) ?: array_key_first(config('ngundang.palettes', []));
        $fontDisplay = $fonts['display'] ?? 'Playfair Display';
        $fontBody    = $fonts['body'] ?? 'Lato';

        $data = $this->previewService->build(
            $paletteKey ?: 'minang',
            $fontDisplay,
            $fontBody,
            [],
            [],
            $template,
        );

        // Render via PageRenderer using the builder document
        $renderer = app(PageRenderer::class);

        $wedding = $data['wedding'];
        $wedding->setRelation('template', $template);

        // Inject sections/events/gifts into the wedding model for widget rendering
        $wedding->setRelation('sections', $data['sections']);

        return $renderer->render(
            $wedding,
            null,
            null,
            null,
            $document,
            editor: true,
        );
    }

    /**
     * Stage unsaved document for the canvas iframe.
     */
    public function stagePreview(Request $request, Template $template)
    {
        $this->authorize('update', $template);

        $input   = $request->input('document');
        $decoded = is_array($input) ? $input : json_decode((string) $input, true);

        if (! is_array($decoded)) {
            return response()->json(['ok' => false, 'error' => 'Dokumen tidak valid.'], 422);
        }

        [$document, $errors] = $this->validator->validate($decoded);

        $request->session()->put("tpl_builder_preview_{$template->id}", $document);

        return response()->json([
            'ok'     => true,
            'errors' => $errors,
            'nodes'  => count($document['nodes']),
        ]);
    }

    /**
     * Persist the document to the template.
     */
    public function update(Request $request, Template $template)
    {
        $this->authorize('update', $template);

        $input   = $request->all();
        
        // Handle both JSON body and form data
        if (empty($input) || !isset($input['nodes'])) {
            $content = $request->getContent();
            $input = json_decode($content, true);
        }

        if (! is_array($input)) {
            return response()->json(['error' => 'Dokumen tidak valid.', 'received' => $request->getContent()], 422);
        }

        [$document, $errors] = $this->validator->validate($input);

        $template->update(['builder_document' => $document]);

        // Sync the draft version with the new document
        $this->versionService->syncDraft($template);

        $this->audit->log('template.builder_saved', 'template', $template->id, [
            'nodes'    => count($document['nodes'] ?? []),
            'overlays' => count($document['overlays'] ?? []),
        ]);

        return response()->json([
            'document' => $document,
            'errors'   => $errors,
            'saved'    => true,
        ]);
    }

    /**
     * Publish the template (creates/publishes a version).
     */
    public function publish(Template $template)
    {
        $this->authorize('update', $template);

        $draft = $template->versions()->where('status', 'draft')->first()
            ?? $this->versionService->createDraft($template);

        // Sync latest document into the draft before publishing
        $draft->update(['document' => $template->builder_document]);

        $this->versionService->publish($draft, request()->user());

        return response()->json([
            'published' => true,
            'version'   => $draft->version,
        ]);
    }

    /**
     * Seed the template document from its default sections.
     */
    public function seed(Template $template)
    {
        $this->authorize('update', $template);

        $document = $this->migrator->fromTemplate($template);

        // If still empty (no default_sections), build a minimal starter document.
        if (empty($document['nodes'])) {
            $document = $this->migrator->starterDocument($template);
        }

        $template->update(['builder_document' => $document]);

        $this->audit->log('template.builder_seeded', 'template', $template->id, [
            'nodes' => count($document['nodes'] ?? []),
        ]);

        return response()->json(['document' => $document]);
    }

    /**
     * Clear the builder document (revert to no document).
     */
    public function destroy(Template $template)
    {
        $this->authorize('update', $template);

        $template->update(['builder_document' => null]);

        $this->audit->log('template.builder_reverted', 'template', $template->id, []);

        return response()->json(['reverted' => true]);
    }

    private function registryPayload(): array
    {
        return [
            'widgets'          => app(WidgetRegistry::class)->toEditorPayload(),
            'decorations'      => app(DecorationRegistry::class)->toEditorPayload(),
            'overlays'         => app(OverlayRegistry::class)->toEditorPayload(),
            'animations'       => app(AnimationRegistry::class)->toEditorPayload(),
            'effects'          => app(EffectRegistry::class)->toEditorPayload(),
            'animationGroups'  => app(AnimationRegistry::class)->groupedByCategory(),
            'animationPresets' => config('builder.animation_presets', []),
            'documentVersion'  => DocumentMigrator::VERSION,
        ];
    }
}
