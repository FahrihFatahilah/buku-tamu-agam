<?php

namespace App\Builder;

use App\Builder\Registry\AnimationRegistry;
use App\Builder\Registry\EffectRegistry;
use App\Builder\Registry\OverlayRegistry;
use App\Builder\Registry\WidgetRegistry;
use App\Models\Template;
use App\Models\Wedding;
use Illuminate\Support\Str;

/**
 * Owns the document schema version and all upgrades between versions.
 *
 * `migrate()` is idempotent and never throws: a missing, partial, legacy or
 * future document always comes back as a valid current-version document. That
 * is what lets old invitations keep working as the builder evolves.
 */
class DocumentMigrator
{
    public const VERSION = 2;

    /**
     * Legacy section key -> document widget type. Lets an invitation that only
     * has `wedding_sections` rows be expressed as a document without losing
     * order, enabled state, titles or per-section text.
     */
    private const SECTION_WIDGET_MAP = [
        'opening' => 'opening',
        'hero' => 'hero',
        'couple' => 'couple-names',
        'quote' => 'quote',
        'countdown' => 'countdown',
        'event' => 'event-details',
        'venue' => 'location',
        'maps' => 'maps',
        'love_story' => 'love-story',
        'gallery' => 'gallery',
        'video' => 'video',
        'rsvp' => 'rsvp',
        'guest_book' => 'guestbook',
        'gift' => 'gift',
        'timeline' => 'timeline',
        'closing' => 'closing',
    ];

    public function __construct(
        private WidgetRegistry $widgets,
        private OverlayRegistry $overlays,
        private AnimationRegistry $animations,
        private EffectRegistry $effects,
    ) {}

    /**
     * Normalise any input into a valid current-version document.
     *
     * @param  array|null  $document  Raw stored document (may be legacy/null).
     * @param  Wedding|Template|null  $subject  Used to synthesise a document when none exists.
     */
    public function migrate(?array $document, Wedding|Template|null $subject = null): array
    {
        if (! $this->looksLikeDocument($document)) {
            return $this->fromSubject($subject);
        }

        $version = (int) ($document['version'] ?? 1);

        // Future versions: keep what we understand rather than discarding it.
        if ($version > self::VERSION) {
            $document['version'] = self::VERSION;
        }

        return $this->normalize($document, $subject);
    }

    /**
     * Synthesise a document from whatever the subject already has:
     * a Wedding from its section rows, a Template from its stored layout.
     */
    private function fromSubject(Wedding|Template|null $subject): array
    {
        return match (true) {
            $subject instanceof Wedding => $this->fromWedding($subject),
            $subject instanceof Template => $this->fromTemplate($subject),
            default => $this->empty(),
        };
    }

    /**
     * A document is anything carrying a node list. Everything else (null, empty
     * array, `default_sections` rows, stray settings blobs) is treated as
     * legacy and synthesised from the subject.
     */
    public function looksLikeDocument(?array $document): bool
    {
        return is_array($document) && is_array($document['nodes'] ?? null);
    }

    /**
     * Build a v2 document from a wedding's existing section rows + theme.
     * This is the backward-compatibility bridge: nothing is written until saved.
     */
    public function fromWedding(?Wedding $wedding): array
    {
        if (! $wedding) {
            return $this->empty();
        }

        $nodes = [];

        $sections = $wedding->sections()->orderBy('sort_order')->get();

        foreach ($sections as $section) {
            $type = self::SECTION_WIDGET_MAP[$section->section_key] ?? null;

            if (! $type || ! $this->widgets->has($type)) {
                continue;
            }

            $settings = is_array($section->settings) ? $section->settings : [];

            $nodes[] = [
                'id' => $this->id('sec'),
                'type' => 'section',
                'props' => ['label' => $section->title ?: $section->section_key],
                'styles' => $this->stylesFromSectionSettings($settings),
                'children' => [[
                    'id' => $this->id('w'),
                    'type' => $type,
                    'props' => $this->propsFromSection($type, $section->title, $settings),
                    'styles' => $this->defaultStylesFor($type),
                    'children' => [],
                ]],
                // Legacy sections that were switched off stay switched off.
                'disabled' => ! $section->is_enabled,
            ];
        }

        return $this->normalize([
            'version' => self::VERSION,
            'theme' => $this->themeFromWedding($wedding),
            'nodes' => $nodes,
            'overlays' => [],
            'effects' => [],
        ], $wedding);
    }

    /**
     * Build a v2 document from a template's stored section layout + theme.
     *
     * Parallels fromWedding(): opening the builder on an existing template
     * shows its current sections as document nodes rather than a blank page.
     */
    public function fromTemplate(Template $template): array
    {
        $nodes = [];

        $layout = is_array($template->default_sections) ? $template->default_sections : [];

        foreach ($layout as $entry) {
            $key = is_array($entry) ? ($entry['key'] ?? null) : $entry;

            if (! is_string($key)) {
                continue;
            }

            $type = self::SECTION_WIDGET_MAP[$key] ?? null;

            if (! $type || ! $this->widgets->has($type)) {
                continue;
            }

            $settings = (is_array($entry) && is_array($entry['settings'] ?? null)) ? $entry['settings'] : [];
            $title = is_array($entry) ? ($entry['title'] ?? null) : null;

            $nodes[] = [
                'id' => $this->id('sec'),
                'type' => 'section',
                'props' => ['label' => $title ?: $key],
                'styles' => $this->stylesFromSectionSettings($settings),
                'children' => [[
                    'id' => $this->id('w'),
                    'type' => $type,
                    'props' => $this->propsFromSection($type, $title, $settings),
                    'styles' => $this->defaultStylesFor($type),
                    'children' => [],
                ]],
                'disabled' => is_array($entry) ? (($entry['enabled'] ?? true) === false) : false,
            ];
        }

        return $this->normalize([
            'version' => self::VERSION,
            'theme' => $this->themeFromTemplate($template),
            'nodes' => $nodes,
            'overlays' => [],
            'effects' => [],
        ], $template);
    }

    /**
     * Fill in anything missing and drop anything unusable. Deliberately total:
     * the renderer must never receive a document it cannot iterate.
     */
    public function normalize(array $document, Wedding|Template|null $subject = null): array
    {
        $theme = is_array($document['theme'] ?? null) ? $document['theme'] : [];

        return [
            'version' => self::VERSION,
            'theme' => $this->normalizeTheme($theme, $subject),
            'nodes' => $this->normalizeNodes($document['nodes'] ?? []),
            'overlays' => $this->normalizeOverlays($document['overlays'] ?? []),
            'effects' => $this->normalizeEffects($document['effects'] ?? []),
        ];
    }

    public function empty(): array
    {
        return [
            'version' => self::VERSION,
            'theme' => $this->normalizeTheme([], null),
            'nodes' => [],
            'overlays' => [],
            'effects' => [],
        ];
    }

    // ── Internals ──────────────────────────────────────────────────────────

    private function normalizeTheme(array $theme, Wedding|Template|null $subject): array
    {
        $defaults = [
            'colors' => [
                'primary' => '#7C3238',
                'secondary' => '#F5F0E8',
                'accent' => '#B8960C',
                'background' => '#FFFFFF',
                'text' => '#2C1810',
            ],
            'typography' => [
                'headingFont' => 'Playfair Display',
                'bodyFont' => 'Lato',
            ],
            'radius' => 4,
            'spacing' => ['sectionY' => 80],
        ];

        $colors = array_merge($defaults['colors'], is_array($theme['colors'] ?? null) ? $theme['colors'] : []);
        $typography = array_merge($defaults['typography'], is_array($theme['typography'] ?? null) ? $theme['typography'] : []);

        // Fall back to the wedding's appearance overrides when the document has none.
        $wedding = $subject instanceof Wedding ? $subject : null;

        if ($wedding && is_array($wedding->appearance)) {
            $a = $wedding->appearance;
            $colors['primary'] = $colors['primary'] ?: ($a['primary_color'] ?? $defaults['colors']['primary']);
            $colors['accent'] = $colors['accent'] ?: ($a['accent_color'] ?? $defaults['colors']['accent']);
            $colors['background'] = $colors['background'] ?: ($a['bg_color'] ?? $defaults['colors']['background']);
            $typography['headingFont'] = $typography['headingFont'] ?: ($a['font_display'] ?? $defaults['typography']['headingFont']);
            $typography['bodyFont'] = $typography['bodyFont'] ?: ($a['font_body'] ?? $defaults['typography']['bodyFont']);
        }

        return [
            'colors' => $colors,
            'typography' => $typography,
            'radius' => $theme['radius'] ?? $defaults['radius'],
            'spacing' => array_merge($defaults['spacing'], is_array($theme['spacing'] ?? null) ? $theme['spacing'] : []),
        ];
    }

    private function normalizeNodes(mixed $nodes, int $depth = 0): array
    {
        if (! is_array($nodes) || $depth > (int) config('builder.max_depth', 8)) {
            return [];
        }

        $out = [];

        foreach ($nodes as $node) {
            if (! is_array($node)) {
                continue;
            }

            $type = $node['type'] ?? null;

            // Unknown types are dropped rather than rendered or persisted.
            if (! is_string($type) || ! $this->isKnownType($type)) {
                continue;
            }

            $normalized = [
                'id' => $this->safeId($node['id'] ?? null),
                'type' => $type,
                'props' => is_array($node['props'] ?? null) ? $node['props'] : [],
                'styles' => $this->normalizeStyles($node['styles'] ?? []),
                'children' => $this->normalizeNodes($node['children'] ?? [], $depth + 1),
            ];

            if (! empty($node['name']) && is_string($node['name'])) {
                $normalized['name'] = Str::limit($node['name'], 80, '');
            }

            if (! empty($node['locked'])) {
                $normalized['locked'] = true;
            }

            if (! empty($node['disabled'])) {
                $normalized['disabled'] = true;
            }

            $animation = $this->normalizeAnimation($node['animation'] ?? null);
            if ($animation) {
                $normalized['animation'] = $animation;
            }

            $effects = $this->normalizeEffects($node['effects'] ?? []);
            if ($effects) {
                $normalized['effects'] = $effects;
            }

            $out[] = $normalized;
        }

        return $out;
    }

    private function normalizeStyles(mixed $styles): array
    {
        if (! is_array($styles)) {
            return [];
        }

        // Accept both a flat map (treated as desktop) and per-breakpoint maps.
        $isBreakpointMap = (bool) array_intersect(array_keys($styles), ['desktop', 'tablet', 'mobile']);

        if (! $isBreakpointMap) {
            return ['desktop' => $styles];
        }

        $out = [];
        foreach (['desktop', 'tablet', 'mobile'] as $breakpoint) {
            if (is_array($styles[$breakpoint] ?? null)) {
                $out[$breakpoint] = $styles[$breakpoint];
            }
        }

        return $out;
    }

    private function normalizeAnimation(mixed $animation): array
    {
        if (! is_array($animation)) {
            return [];
        }

        $out = [];

        foreach (['entrance', 'continuous', 'hover', 'scroll'] as $trigger) {
            $config = $animation[$trigger] ?? null;

            if (! is_array($config)) {
                continue;
            }

            // `false` disables a trigger explicitly.
            if (($config['enabled'] ?? true) === false) {
                continue;
            }

            $type = $config['type'] ?? null;

            if (! is_string($type) || ! $this->animations->has($type)) {
                continue;
            }

            $out[$trigger] = array_filter([
                'type' => $type,
                'duration' => isset($config['duration']) ? (int) $config['duration'] : null,
                'delay' => isset($config['delay']) ? (int) $config['delay'] : null,
                'easing' => is_string($config['easing'] ?? null) ? $config['easing'] : null,
                'repeat' => is_string($config['repeat'] ?? null) ? $config['repeat'] : null,
                'intensity' => isset($config['intensity']) ? (float) $config['intensity'] : null,
                'direction' => is_string($config['direction'] ?? null) ? $config['direction'] : null,
            ], fn ($v) => $v !== null);
        }

        return $out;
    }

    private function normalizeOverlays(mixed $overlays): array
    {
        if (! is_array($overlays)) {
            return [];
        }

        $out = [];

        foreach ($overlays as $overlay) {
            if (! is_array($overlay)) {
                continue;
            }

            $type = $overlay['type'] ?? null;

            if (! is_string($type) || ! $this->overlays->has($type)) {
                continue;
            }

            $out[] = [
                'id' => $this->safeId($overlay['id'] ?? null),
                'type' => $type,
                'enabled' => ($overlay['enabled'] ?? true) !== false,
                'mobile' => ($overlay['mobile'] ?? true) !== false,
                'props' => is_array($overlay['props'] ?? null) ? $overlay['props'] : [],
                'styles' => $this->normalizeStyles($overlay['styles'] ?? []),
            ];
        }

        return $out;
    }

    private function normalizeEffects(mixed $effects): array
    {
        if (! is_array($effects)) {
            return [];
        }

        $registry = $this->effects;
        $out = [];

        foreach ($effects as $effect) {
            if (! is_array($effect)) {
                continue;
            }

            $type = $effect['type'] ?? null;

            if (! is_string($type) || ! $registry->has($type)) {
                continue;
            }

            $out[] = [
                'type' => $type,
                'props' => is_array($effect['props'] ?? null) ? $effect['props'] : [],
            ];
        }

        return $out;
    }

    private function isKnownType(string $type): bool
    {
        return $type === 'decoration'
            ? true
            : $this->widgets->has($type);
    }

    private function stylesFromSectionSettings(array $settings): array
    {
        $desktop = [];

        foreach ([
            'bg_color' => 'backgroundColor',
            'bg_image' => 'backgroundImage',
        ] as $from => $to) {
            if (! empty($settings[$from])) {
                $desktop[$to] = $settings[$from];
            }
        }

        if (! empty($settings['overlay_color'])) {
            $desktop['overlayColor'] = $settings['overlay_color'];
        }

        if (isset($settings['overlay_opacity'])) {
            $desktop['overlayOpacity'] = (int) $settings['overlay_opacity'];
        }

        return $desktop ? ['desktop' => $desktop] : [];
    }

    private function propsFromSection(string $type, ?string $title, array $settings): array
    {
        $props = $this->defaultPropsFor($type);
        $text = is_array($settings['text'] ?? null) ? $settings['text'] : [];

        // Legacy inline text maps onto the widget's own prop names.
        foreach ($text as $field => $value) {
            if (is_string($value) && $value !== '') {
                $props[$field] = $value;
            }
        }

        if ($title && ! isset($props['heading'])) {
            $props['heading'] = $title;
        }

        // List-driven widgets carried their items in section settings.
        if (is_array($settings['items'] ?? null) && $type === 'timeline') {
            $props['items'] = $settings['items'];
        }

        if (is_array($settings['stories'] ?? null) && $type === 'love-story') {
            $props['stories'] = $settings['stories'];
        }

        return array_filter($props, fn ($v) => $v !== null);
    }

    private function defaultPropsFor(string $type): array
    {
        return $this->widgets->get($type)['defaultProps'] ?? [];
    }

    private function defaultStylesFor(string $type): array
    {
        return $this->widgets->get($type)['defaultStyles'] ?? [];
    }

    private function themeFromWedding(Wedding $wedding): array
    {
        $settings = $wedding->template?->default_settings ?? [];
        $palette = is_array($settings['palette'] ?? null) ? $settings['palette'] : [];
        $fonts = is_array($settings['fonts'] ?? null) ? $settings['fonts'] : [];
        $appearance = is_array($wedding->appearance) ? $wedding->appearance : [];

        return [
            'colors' => [
                'primary' => $appearance['primary_color'] ?? $palette['primary'] ?? '#7C3238',
                'secondary' => $appearance['secondary_color'] ?? $palette['secondary'] ?? '#F5F0E8',
                'accent' => $appearance['accent_color'] ?? $palette['accent'] ?? '#B8960C',
                'background' => $appearance['bg_color'] ?? $palette['secondary'] ?? '#FFFFFF',
                'text' => $palette['dark'] ?? '#2C1810',
            ],
            'typography' => [
                'headingFont' => $appearance['font_display'] ?? $fonts['display'] ?? 'Playfair Display',
                'bodyFont' => $appearance['font_body'] ?? $fonts['body'] ?? 'Lato',
            ],
        ];
    }

    /**
     * The template's own palette and fonts. A template has no appearance
     * overrides, so this is themeFromWedding() minus that layer.
     */
    private function themeFromTemplate(Template $template): array
    {
        $settings = is_array($template->default_settings) ? $template->default_settings : [];
        $palette = is_array($settings['palette'] ?? null) ? $settings['palette'] : [];
        $fonts = is_array($settings['fonts'] ?? null) ? $settings['fonts'] : [];

        return [
            'colors' => [
                'primary' => $palette['primary'] ?? '#7C3238',
                'secondary' => $palette['secondary'] ?? '#F5F0E8',
                'accent' => $palette['accent'] ?? '#B8960C',
                'background' => $palette['secondary'] ?? '#FFFFFF',
                'text' => $palette['dark'] ?? '#2C1810',
            ],
            'typography' => [
                'headingFont' => $fonts['display'] ?? 'Playfair Display',
                'bodyFont' => $fonts['body'] ?? 'Lato',
            ],
        ];
    }

    private function safeId(mixed $id): string
    {
        if (is_string($id) && preg_match('/^[A-Za-z0-9_-]{1,64}$/', $id)) {
            return $id;
        }

        return $this->id('n');
    }

    private function id(string $prefix): string
    {
        return $prefix.'_'.Str::lower(Str::random(8));
    }
}
