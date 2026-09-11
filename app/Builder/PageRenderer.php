<?php

namespace App\Builder;

use App\Builder\Registry\OverlayRegistry;
use App\Builder\Registry\WidgetRegistry;
use App\Models\Guest;
use App\Models\Wedding;
use App\Services\TemplateRenderer;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;
use Illuminate\View\View;

/**
 * The single document renderer.
 *
 * Used by both the editor canvas and the public invitation page, which is what
 * guarantees "what you see in the editor is what visitors get". It renders
 * server-side so the public page never loads any builder JavaScript.
 */
class PageRenderer
{
    public function __construct(
        private WidgetRegistry $widgets,
        private OverlayRegistry $overlays,
        private DocumentMigrator $migrator,
        private StyleCompiler $styles,
        private TemplateRenderer $invitation,
    ) {}

    /**
     * Render a wedding's document to the public/canvas view.
     *
     * @param  Collection|null  $hiddenGiftIds  Guest visibility filtering carried over.
     * @param  array|null  $document  Override document (editor preview with unsaved state).
     */
    public function render(
        Wedding $wedding,
        ?Guest $guest = null,
        ?Collection $hiddenGiftIds = null,
        ?Collection $hiddenEventIds = null,
        ?array $document = null,
        bool $editor = false,
    ): View {
        $document = $this->migrator->migrate($document ?? $wedding->builder_document, $wedding);

        $data = $this->buildViewData($wedding, $guest, $hiddenGiftIds, $hiddenEventIds, $document, $editor);

        return view('invitation.document', $data);
    }

    public function buildViewData(
        Wedding $wedding,
        ?Guest $guest,
        ?Collection $hiddenGiftIds,
        ?Collection $hiddenEventIds,
        array $document,
        bool $editor = false,
    ): array {
        // Reuse the existing invitation view data (events, gifts, media, qr,
        // playlist, guest visibility) so the document layer never duplicates
        // business data or re-implements visibility rules.
        $invitation = $this->invitation->buildViewData($wedding, $guest, $hiddenGiftIds, $hiddenEventIds);

        $context = $invitation + [
            'document' => $document,
            'theme' => $document['theme'] ?? [],
            'widgets' => $this->widgets,
            'overlayRegistry' => $this->overlays,
            'animationConfig' => $this->animationConfig($wedding),
            'isEditor' => $editor,
        ];

        // Pre-render everything so the layout only echoes strings.
        $nodesHtml = '';
        foreach ($document['nodes'] ?? [] as $node) {
            if (! is_array($node) || ! empty($node['disabled'])) {
                continue;
            }

            $nodesHtml .= $this->renderNode($node, $context) ?? '';
        }

        $behindHtml = '';
        $frontHtml = '';
        foreach ($document['overlays'] ?? [] as $overlay) {
            if (! is_array($overlay) || empty($overlay['enabled'])) {
                continue;
            }

            $html = $this->renderOverlay($overlay, $context);

            if ($html === null) {
                continue;
            }

            // Negative z-index overlays sit behind the content.
            $z = $overlay['styles']['desktop']['zIndex'] ?? 30;

            if (is_numeric($z) && (int) $z < 0) {
                $behindHtml .= $html;
            } else {
                $frontHtml .= $html;
            }
        }

        return $context + [
            'nodes' => $document['nodes'] ?? [],
            'overlays' => $document['overlays'] ?? [],
            'nodesHtml' => $nodesHtml,
            'overlaysBehindHtml' => $behindHtml,
            'overlaysFrontHtml' => $frontHtml,
            'compiledStyles' => $this->styles->compile($document),
        ];
    }

    /**
     * Render an overlay layer.
     */
    public function renderOverlay(array $overlay, array $context): ?string
    {
        $type = $overlay['type'] ?? null;

        if (! is_string($type)) {
            return null;
        }

        $view = $this->overlays->view($type);

        if (! $view || ! view()->exists($view)) {
            return null;
        }

        try {
            return view($view, $context + ['overlay' => $overlay])->render();
        } catch (\Throwable $e) {
            Log::warning('Builder overlay failed to render', [
                'type' => $type,
                'overlay' => $overlay['id'] ?? null,
                'error' => $e->getMessage(),
            ]);

            return null;
        }
    }

    /**
     * Global animation settings. Now actually applied (previously written by
     * the Appearance page but read by nothing).
     */
    public function animationConfig(Wedding $wedding): array
    {
        $config = is_array($wedding->animation_config) ? $wedding->animation_config : [];
        $presets = config('builder.animation_presets', []);
        $preset = $config['preset'] ?? 'elegant';
        $presetDef = $presets[$preset] ?? ($presets['elegant'] ?? ['duration' => 1.0, 'intensity' => 1.0]);

        $durationScale = match ($config['duration'] ?? 'normal') {
            'fast' => 0.7,
            'slow' => 1.4,
            default => 1.0,
        };

        $intensityScale = match ($config['intensity'] ?? 'normal') {
            'subtle' => 0.6,
            'strong' => 1.3,
            default => 1.0,
        };

        return [
            'preset' => $preset,
            'disabled' => $preset === 'none',
            'durationScale' => $durationScale * ($presetDef['duration'] ?? 1.0),
            'intensityScale' => $intensityScale * ($presetDef['intensity'] ?? 1.0),
        ];
    }

    /**
     * Render a node to a complete, wrapped HTML element.
     *
     * The renderer owns the wrapper (tag, node id, animation attributes) and
     * container recursion, so widget views only ever render their own content.
     * That keeps widgets small and gives every node consistent selection and
     * animation hooks.
     *
     * Returns null when the widget is unknown or requires a guest that is not
     * present — the renderer then skips it rather than erroring.
     */
    public function renderNode(array $node, array $context): ?string
    {
        $type = $node['type'] ?? null;

        if (! is_string($type)) {
            return null;
        }

        if ($type === 'decoration') {
            return $this->renderDecoration($node, $context);
        }

        $definition = $this->widgets->get($type);
        $view = $definition['view'] ?? null;

        if (! $view || ! view()->exists($view)) {
            return null;
        }

        // Guest-specific widgets disappear on a non-personalized invitation.
        if (! empty($definition['requiresGuest']) && empty($context['guest'])) {
            return null;
        }

        try {
            // Pre-render children so container widgets just place the HTML.
            $childrenHtml = '';
            foreach ($node['children'] ?? [] as $child) {
                if (! is_array($child) || ! empty($child['disabled'])) {
                    continue;
                }

                $childrenHtml .= $this->renderNode($child, $context) ?? '';
            }

            $inner = view($view, $context + [
                'node' => $node,
                'widget' => $definition,
                'childrenHtml' => $childrenHtml,
            ])->render();
        } catch (\Throwable $e) {
            // One broken widget must not take down a live invitation.
            Log::warning('Builder widget failed to render', [
                'type' => $type,
                'node' => $node['id'] ?? null,
                'error' => $e->getMessage(),
            ]);

            return null;
        }

        return $this->wrap($node, $definition, $inner, $context);
    }

    /**
     * Build the wrapper element for a node.
     */
    private function wrap(array $node, array $definition, string $inner, array $context): string
    {
        $tag = $definition['tag'] ?? 'div';

        if (! preg_match('/^[a-z][a-z0-9]*$/', $tag)) {
            $tag = 'div';
        }

        $attributes = [
            'data-node-id' => (string) ($node['id'] ?? ''),
            'data-node-type' => (string) ($node['type'] ?? ''),
        ];

        if (! empty($node['name'])) {
            $attributes['data-node-name'] = (string) $node['name'];
        }

        $attributes += $this->animationAttributes($node, $context);

        $rendered = '';
        foreach ($attributes as $name => $value) {
            if ($value === null || $value === '') {
                continue;
            }

            $rendered .= sprintf(' %s="%s"', $name, e((string) $value));
        }

        return "<{$tag}{$rendered}>{$inner}</{$tag}>";
    }

    /**
     * Animation + effect hooks consumed by the public runtime.
     */
    private function animationAttributes(array $node, array $context): array
    {
        $config = $context['animationConfig'] ?? [];
        $attributes = [];

        // `none` preset disables every animation document-wide.
        if (! empty($config['disabled'])) {
            return [];
        }

        $animation = is_array($node['animation'] ?? null) ? $node['animation'] : [];

        foreach (['entrance', 'continuous', 'scroll'] as $trigger) {
            $entry = $animation[$trigger] ?? null;

            if (! is_array($entry) || empty($entry['type'])) {
                continue;
            }

            $duration = (int) ($entry['duration'] ?? 700);
            $duration = (int) round($duration * ($config['durationScale'] ?? 1.0));
            $duration = max(0, min(20000, $duration));

            $attributes["data-anim-{$trigger}"] = (string) $entry['type'];
            $attributes["data-anim-{$trigger}-duration"] = (string) $duration;

            if ($trigger === 'entrance') {
                $delay = (int) ($entry['delay'] ?? 0);
                $attributes['data-anim-delay'] = (string) max(0, min(10000, $delay));

                if (($entry['repeat'] ?? null) === 'loop') {
                    $attributes['data-anim-repeat'] = 'loop';
                }
            }

            if ($trigger === 'scroll' && isset($entry['intensity'])) {
                $attributes['data-anim-speed'] = (string) round(((float) $entry['intensity']) * ($config['intensityScale'] ?? 1.0), 3);
            }
        }

        // Trigger an entrance animation when scrolled into view.
        if (isset($attributes['data-anim-entrance'])) {
            $attributes['data-anim-watch'] = '1';
        }

        if (! empty($node['effects']) && is_array($node['effects'])) {
            $names = [];
            foreach ($node['effects'] as $effect) {
                if (is_array($effect) && ! empty($effect['type'])) {
                    $names[] = (string) $effect['type'];
                }
            }

            if ($names) {
                $attributes['data-effects'] = implode(',', $names);
            }
        }

        return $attributes;
    }

    private function renderDecoration(array $node, array $context): ?string
    {
        try {
            return view('invitation.decorations.asset', $context + ['node' => $node])->render();
        } catch (\Throwable $e) {
            Log::warning('Builder decoration failed to render', [
                'node' => $node['id'] ?? null,
                'error' => $e->getMessage(),
            ]);

            return null;
        }
    }

    /**
     * Resolve the URL for a decoration asset token or uploaded path.
     */
    public function decorationUrl(array $node): ?string
    {
        $asset = $node['props']['asset'] ?? null;

        if (! is_string($asset) || $asset === '') {
            return null;
        }

        if (str_starts_with($asset, 'builtin:')) {
            $name = substr($asset, 8);

            return preg_match('/^[a-z0-9-]{1,40}$/', $name)
                ? asset("builder/decorations/{$name}.svg")
                : null;
        }

        if (preg_match('#^[A-Za-z0-9][A-Za-z0-9/_.-]*\.(jpg|jpeg|png|webp|gif|svg|avif)$#i', $asset)
            && ! str_contains($asset, '..')) {
            return \Storage::url($asset);
        }

        return null;
    }
}
