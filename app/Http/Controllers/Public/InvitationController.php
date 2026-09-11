<?php

namespace App\Http\Controllers\Public;

use App\Builder\PageRenderer;
use App\Http\Controllers\Controller;
use App\Services\Builder\DocumentService;
use App\Services\InvitationService;
use App\Services\TemplateRenderer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class InvitationController extends Controller
{
    public function __construct(
        private InvitationService $invitationService,
        private TemplateRenderer $renderer,
        private DocumentService $documents,
        private PageRenderer $pageRenderer,
    ) {}

    // ── Long URL: /INV-XXXXXX/slug ─────────────────────────────────────────

    public function show(Request $request, string $publicId, string $slug)
    {
        $result = $this->invitationService->resolvePublic($request->getHost(), $publicId, $slug);

        return $this->renderPublic($result);
    }

    public function showPersonalized(Request $request, string $publicId, string $slug, string $token)
    {
        $result = $this->invitationService->resolvePersonalized($request->getHost(), $publicId, $slug, $token);

        return $this->renderPersonalized($result, $token);
    }

    // ── Short URL: /xxxxxx/slug ────────────────────────────────────────────

    public function showShort(Request $request, string $shortId, string $slug)
    {
        $result = $this->invitationService->resolvePublicShort($request->getHost(), $shortId, $slug);

        return $this->renderPublic($result);
    }

    public function showShortPersonalized(Request $request, string $shortId, string $slug, string $token)
    {
        $result = $this->invitationService->resolvePersonalizedShort($request->getHost(), $shortId, $slug, $token);

        return $this->renderPersonalized($result, $token);
    }

    // ── Renderers ──────────────────────────────────────────────────────────

    private function renderPublic(array $result)
    {
        if ($result['redirect']) {
            return redirect($result['redirect'], 301);
        }
        if ($result['error']) {
            abort(404);
        }

        $wedding = $result['wedding'];

        // Opt-in: only weddings carrying a document render through the builder.
        if ($document = $this->documents->forRender($wedding)) {
            return $this->renderDocument($wedding, null, $document);
        }

        $templateKey = $wedding->template?->key ?? 'minang-elegance';

        return view($this->renderer->layoutView($templateKey),
            $this->renderer->buildViewData($wedding, null));
    }

    private function renderPersonalized(array $result, string $token)
    {
        if ($result['redirect']) {
            return redirect("{$result['redirect']}/u/{$token}", 301);
        }
        if ($result['error']) {
            abort(404);
        }

        $wedding = $result['wedding'];

        if ($document = $this->documents->forRender($wedding)) {
            return $this->renderDocument(
                $wedding,
                $result['guest'],
                $document,
                $result['hidden_gifts'] ?? null,
                $result['hidden_events'] ?? null,
            );
        }

        $templateKey = $wedding->template?->key ?? 'minang-elegance';

        return view($this->renderer->layoutView($templateKey),
            $this->renderer->buildViewData(
                $wedding,
                $result['guest'],
                $result['hidden_gifts'],
                $result['hidden_events'],
            ));
    }

    /**
     * Document rendering path. A failure here must never take a live
     * invitation down, so it falls back to the coded template and logs.
     */
    private function renderDocument(
        $wedding,
        $guest,
        array $document,
        $hiddenGiftIds = null,
        $hiddenEventIds = null,
    ) {
        try {
            return $this->pageRenderer->render($wedding, $guest, $hiddenGiftIds, $hiddenEventIds, $document);
        } catch (\Throwable $e) {
            Log::error('Builder document render failed; falling back to template', [
                'wedding' => $wedding->id,
                'error' => $e->getMessage(),
            ]);

            $templateKey = $wedding->template?->key ?? 'minang-elegance';

            return view($this->renderer->layoutView($templateKey),
                $this->renderer->buildViewData($wedding, $guest, $hiddenGiftIds, $hiddenEventIds));
        }
    }
}
