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
use App\Http\Requests\Admin\UpdateBuilderDocumentRequest;
use App\Models\Wedding;
use App\Services\Builder\DocumentService;
use App\Services\GuestVisibilityService;
use App\Services\WeddingService;
use Illuminate\Http\Request;

/**
 * Thin controller for the visual invitation builder. Persistence rules live in
 * DocumentService, rendering in PageRenderer.
 */
class InvitationBuilderController extends Controller
{
    public function __construct(
        private DocumentService $documents,
        private PageRenderer $renderer,
        private GuestVisibilityService $visibility,
        private DocumentValidator $validator,
    ) {}

    /**
     * The editor shell.
     */
    public function edit(Wedding $wedding)
    {
        $this->authorize('buildDocument', $wedding);

        $document = $this->documents->forEditor($wedding);

        return view('builder.editor', [
            'wedding' => $wedding,
            'document' => $document,
            'registry' => $this->registryPayload(),
            // Needed by the bank-account inspector field.
            'giftMethods' => $wedding->giftMethods()->orderBy('sort_order')->get(),
            'previewUrl' => route('admin.weddings.builder.preview', $wedding),
            'stageUrl' => route('admin.weddings.builder.preview.stage', $wedding),
            'saveUrl' => route('admin.weddings.builder.document.update', $wedding),
            'enableUrl' => route('admin.weddings.builder.enable', $wedding),
            'revertUrl' => route('admin.weddings.builder.document.destroy', $wedding),
            'publishUrl' => route('admin.weddings.builder.publish', $wedding),
            'isActive' => $wedding->hasBuilderDocument(),
            'usesCodedTemplate' => ! $wedding->hasBuilderDocument() && $wedding->template !== null,
            'templateKey' => $wedding->template?->key,
        ]);
    }

    /**
     * Server-rendered canvas. Uses exactly the same renderer as the public
     * page, which is what keeps editor and published output identical.
     */
    public function preview(Request $request, Wedding $wedding)
    {
        $this->authorize('buildDocument', $wedding);

        // Preview the staged (unsaved) document when the editor has one.
        $document = $request->session()->get("builder_preview_{$wedding->id}");

        if (! is_array($document)) {
            $document = $this->documents->forEditor($wedding);
        }

        // The editor canvas is a non-personalized render: pass no guest.
        return $this->renderer->render($wedding, null, null, null, $document, editor: true);
    }

    /**
     * Stage the editor's unsaved document, then the canvas reloads.
     *
     * Staging avoids putting the whole document in a URL, and the document is
     * validated first because it will be rendered on the next request.
     */
    public function stagePreview(Request $request, Wedding $wedding)
    {
        $this->authorize('buildDocument', $wedding);

        // The editor posts a JSON body, so the document arrives already
        // decoded as an array; a form post sends it as a JSON string.
        $input = $request->input('document');

        $decoded = is_array($input) ? $input : json_decode((string) $input, true);

        if (! is_array($decoded)) {
            return response()->json(['ok' => false, 'error' => 'Dokumen tidak valid.'], 422);
        }

        [$document, $errors] = $this->validator->validate($decoded, $wedding);

        $request->session()->put("builder_preview_{$wedding->id}", $document);

        return response()->json([
            'ok' => true,
            'errors' => $errors,
            'nodes' => count($document['nodes']),
        ]);
    }

    /**
     * The current document as JSON.
     */
    public function show(Wedding $wedding)
    {
        $this->authorize('buildDocument', $wedding);

        return response()->json([
            'document' => $this->documents->forEditor($wedding),
            'active' => $wedding->hasBuilderDocument(),
        ]);
    }

    /**
     * Persist the document.
     */
    public function update(UpdateBuilderDocumentRequest $request, Wedding $wedding)
    {
        $result = $this->documents->save($wedding, $request->validated());

        return response()->json([
            'document' => $result['document'],
            'errors' => $result['errors'],
            'saved' => true,
        ]);
    }

    /**
     * Publish: materialise the document if needed and publish the wedding.
     */
    public function publish(Wedding $wedding)
    {
        $this->authorize('buildDocument', $wedding);

        $this->documents->enable($wedding);

        app(WeddingService::class)->publish($wedding);

        return response()->json([
            'published' => true,
            'url' => $wedding->publicUrl(),
        ]);
    }

    /**
     * Opt in to the document renderer (synthesises from the existing sections).
     */
    public function enable(Wedding $wedding)
    {
        $this->authorize('buildDocument', $wedding);

        return response()->json([
            'document' => $this->documents->enable($wedding),
        ]);
    }

    /**
     * Seed the document from the wedding's template.
     */
    public function seed(Wedding $wedding)
    {
        $this->authorize('buildDocument', $wedding);

        $document = $this->documents->seedFromTemplate($wedding);

        $wedding->update(['builder_document' => $document]);

        return response()->json(['document' => $document]);
    }

    /**
     * Revert to the coded template.
     */
    public function destroy(Wedding $wedding)
    {
        $this->authorize('buildDocument', $wedding);

        $this->documents->revert($wedding);

        return response()->json(['reverted' => true]);
    }

    /**
     * Everything the editor needs to build its palette and inspector.
     */
    private function registryPayload(): array
    {
        return [
            'widgets' => app(WidgetRegistry::class)->toEditorPayload(),
            'decorations' => app(DecorationRegistry::class)->toEditorPayload(),
            'overlays' => app(OverlayRegistry::class)->toEditorPayload(),
            'animations' => app(AnimationRegistry::class)->toEditorPayload(),
            'effects' => app(EffectRegistry::class)->toEditorPayload(),
            'animationGroups' => app(AnimationRegistry::class)->groupedByCategory(),
            'animationPresets' => config('builder.animation_presets', []),
            'documentVersion' => DocumentMigrator::VERSION,
        ];
    }
}
