<?php

namespace App\Builder;

use App\Builder\Registry\AnimationRegistry;
use App\Builder\Registry\DecorationRegistry;
use App\Builder\Registry\EffectRegistry;
use App\Builder\Registry\OverlayRegistry;
use App\Builder\Registry\WidgetRegistry;
use App\Models\Wedding;
use Illuminate\Support\Str;

/**
 * Validates and sanitises an incoming document before it is persisted.
 *
 * Security posture: nothing is trusted. Unknown widget types, unknown props,
 * unknown style keys, malformed ids and unsafe values are all dropped rather
 * than stored. Combined with the renderer's registry lookup, this means a
 * client can never inject markup, script or arbitrary CSS through the builder.
 */
class DocumentValidator
{
    public function __construct(
        private WidgetRegistry $widgets,
        private DecorationRegistry $decorations,
        private EffectRegistry $effects,
        private OverlayRegistry $overlays,
        private AnimationRegistry $animations,
        private DocumentMigrator $migrator,
    ) {}

    /** Style keys the compiler understands. Anything else is discarded. */
    private const STYLE_KEYS = [
        'fontSize', 'fontWeight', 'letterSpacing', 'lineHeight', 'textAlign',
        'color', 'backgroundColor', 'borderColor',
        'backgroundImage', 'backgroundGradient', 'backgroundSize',
        'padding', 'margin', 'borderWidth', 'borderStyle', 'borderRadius', 'boxShadow',
        'opacity', 'width', 'height', 'maxWidth', 'minHeight', 'gap',
        'x', 'y', 'rotation', 'scale', 'flipH', 'flipV', 'translateX', 'translateY',
        'zIndex', 'blur', 'blendMode', 'visible',
        'justifyContent', 'alignItems', 'flexDirection', 'display', 'position', 'overflow', 'objectFit',
        'overlayColor', 'overlayOpacity',
    ];

    /** Decoration-only style keys. */
    private const DECORATION_STYLE_KEYS = [
        'x', 'y', 'width', 'height', 'rotation', 'scale', 'opacity', 'zIndex',
        'blur', 'flipH', 'flipV', 'blendMode', 'visible', 'padding', 'margin',
    ];

    private const ANIMATION_TRIGGERS = ['entrance', 'continuous', 'hover', 'scroll'];

    private const EASINGS = ['linear', 'ease', 'ease-in', 'ease-out', 'ease-in-out'];

    /**
     * @param  array  $input  Raw payload from the client.
     * @return array{0: array, 1: array} [validated document, errors]
     */
    public function validate(array $input, ?Wedding $wedding = null): array
    {
        $errors = [];

        $size = strlen(json_encode($input) ?: '');

        if ($size > (int) config('builder.max_document_bytes', 262144)) {
            return [$this->migrator->migrate(null, $wedding), ['document' => ['Dokumen terlalu besar.']]];
        }

        $nodes = $this->validateNodes($input['nodes'] ?? [], $errors, 0);
        $overlays = $this->validateOverlays($input['overlays'] ?? [], $errors);

        $document = [
            'version' => DocumentMigrator::VERSION,
            'theme' => $this->validateTheme($input['theme'] ?? []),
            'nodes' => $nodes,
            'overlays' => $overlays,
        ];

        // Re-run the migrator so defaults/uniqueness are applied consistently.
        return [$this->migrator->migrate($document, $wedding), $errors];
    }

    // ── Nodes ──────────────────────────────────────────────────────────────

    private function validateNodes(mixed $nodes, array &$errors, int $depth): array
    {
        if (! is_array($nodes)) {
            return [];
        }

        if ($depth > (int) config('builder.max_depth', 8)) {
            $errors['nodes'][] = 'Struktur terlalu dalam.';

            return [];
        }

        $out = [];
        $seenIds = [];

        foreach ($nodes as $node) {
            if (! is_array($node)) {
                continue;
            }

            $type = $node['type'] ?? null;

            if (! is_string($type)) {
                continue;
            }

            $isDecoration = $type === 'decoration';

            if (! $isDecoration && ! $this->widgets->has($type)) {
                $errors['nodes'][] = "Widget tidak dikenal: {$type}";

                continue;
            }

            $id = $this->uniqueId($node['id'] ?? null, $seenIds);

            $validated = [
                'id' => $id,
                'type' => $type,
                'props' => $isDecoration
                    ? $this->validateDecorationProps($node['props'] ?? [])
                    : $this->validateProps($type, $node['props'] ?? []),
                'styles' => $this->validateStyles($node['styles'] ?? [], $isDecoration),
                'children' => $isDecoration
                    ? []
                    : $this->validateChildren($type, $node, $errors, $depth),
            ];

            if (! empty($node['name']) && is_string($node['name'])) {
                $validated['name'] = Str::limit(trim($node['name']), 80, '');
            }

            if (! empty($node['locked'])) {
                $validated['locked'] = true;
            }

            if (! empty($node['disabled'])) {
                $validated['disabled'] = true;
            }

            if ($animation = $this->validateAnimation($node['animation'] ?? null)) {
                $validated['animation'] = $animation;
            }

            if ($effects = $this->validateEffects($node['effects'] ?? null)) {
                $validated['effects'] = $effects;
            }

            $out[] = $validated;
        }

        return $out;
    }

    private function validateChildren(string $parentType, array $node, array &$errors, int $depth): array
    {
        $children = $node['children'] ?? [];

        if (! is_array($children) || ! $children) {
            return [];
        }

        // Enforce the parent's declared containment rules.
        if (empty($this->widgets->get($parentType)['allowedChildren'])) {
            $errors['nodes'][] = "Widget {$parentType} tidak dapat berisi elemen lain.";

            return [];
        }

        $validated = $this->validateNodes($children, $errors, $depth + 1);

        return array_values(array_filter($validated, function ($child) use ($parentType) {
            return $this->widgets->canContain($parentType, $child['type']);
        }));
    }

    /**
     * Keep only props the widget declares in its inspector schema.
     */
    private function validateProps(string $type, mixed $props): array
    {
        if (! is_array($props)) {
            return [];
        }

        $allowed = $this->declaredPropNames($type);

        // A widget with no declared fields (e.g. section) accepts nothing.
        $out = [];

        foreach ($props as $key => $value) {
            if (! is_string($key) || ! in_array($key, $allowed, true)) {
                continue;
            }

            $clean = $this->sanitizeValue($value);

            if ($clean !== null) {
                $out[$key] = $clean;
            }
        }

        return $out;
    }

    private function validateDecorationProps(mixed $props): array
    {
        if (! is_array($props)) {
            return [];
        }

        $allowed = ['asset', 'color', 'alt'];
        $out = [];

        foreach ($allowed as $key) {
            if (! isset($props[$key])) {
                continue;
            }

            $clean = $this->sanitizeValue($props[$key]);

            if ($clean === null) {
                continue;
            }

            // Asset must be a builtin token or a storage-relative image path.
            if ($key === 'asset' && is_string($clean)) {
                $isBuiltin = (bool) preg_match('/^builtin:[a-z0-9-]{1,40}$/', $clean);
                $isPath = (bool) preg_match('#^[A-Za-z0-9][A-Za-z0-9/_.-]*\.(jpg|jpeg|png|webp|gif|svg|avif)$#i', $clean)
                    && ! str_contains($clean, '..');

                if (! $isBuiltin && ! $isPath) {
                    continue;
                }
            }

            if ($key === 'color' && is_string($clean)
                && ! preg_match('/^#([0-9a-fA-F]{3,8})$/', $clean)) {
                continue;
            }

            $out[$key] = $clean;
        }

        return $out;
    }

    /**
     * Every prop name a widget declares in any inspector tab.
     *
     * @return array<int, string>
     */
    private function declaredPropNames(string $type): array
    {
        $definition = $this->widgets->get($type);
        $inspector = $definition['inspector'] ?? [];

        $names = array_keys($definition['defaultProps'] ?? []);

        foreach ($inspector as $fields) {
            if (! is_array($fields)) {
                continue;
            }

            foreach ($fields as $field) {
                if (is_array($field) && isset($field['name']) && is_string($field['name'])) {
                    $names[] = $field['name'];
                }
            }
        }

        return array_values(array_unique($names));
    }

    // ── Styles ─────────────────────────────────────────────────────────────

    private function validateStyles(mixed $styles, bool $isDecoration): array
    {
        if (! is_array($styles)) {
            return [];
        }

        $allowed = $isDecoration ? self::DECORATION_STYLE_KEYS : self::STYLE_KEYS;

        // Accept either a flat map (treated as desktop) or per-breakpoint maps.
        $isBreakpointMap = (bool) array_intersect(array_keys($styles), ['desktop', 'tablet', 'mobile']);

        if (! $isBreakpointMap) {
            $styles = ['desktop' => $styles];
        }

        $out = [];

        foreach (['desktop', 'tablet', 'mobile'] as $breakpoint) {
            $map = $styles[$breakpoint] ?? null;

            if (! is_array($map)) {
                continue;
            }

            $clean = [];

            foreach ($map as $key => $value) {
                if (! is_string($key) || ! in_array($key, $allowed, true)) {
                    continue;
                }

                $sanitized = $this->sanitizeStyleValue($key, $value);

                if ($sanitized !== null) {
                    $clean[$key] = $sanitized;
                }
            }

            if ($clean) {
                $out[$breakpoint] = $clean;
            }
        }

        return $out;
    }

    /**
     * Style values are only ever numbers, booleans, hex colours, structural
     * arrays, or safe enum strings — never raw CSS.
     */
    private function sanitizeStyleValue(string $key, mixed $value): mixed
    {
        if (in_array($key, ['flipH', 'flipV', 'visible'], true)) {
            return (bool) $value;
        }

        if (in_array($key, ['padding', 'margin'], true)) {
            if (! is_array($value)) {
                return null;
            }

            $box = [];
            foreach (['top', 'right', 'bottom', 'left'] as $side) {
                if (isset($value[$side]) && is_numeric($value[$side])) {
                    $box[$side] = (float) $value[$side];
                }
            }

            return $box ?: null;
        }

        if ($key === 'boxShadow') {
            if (! is_array($value)) {
                return null;
            }

            $shadow = [];
            foreach (['x', 'y', 'blur', 'spread'] as $part) {
                if (isset($value[$part]) && is_numeric($value[$part])) {
                    $shadow[$part] = (float) $value[$part];
                }
            }

            if (! empty($value['inset'])) {
                $shadow['inset'] = true;
            }

            $color = $value['color'] ?? null;
            if (is_string($color) && $this->isSafeColorOrToken($color)) {
                $shadow['color'] = $color;
            }

            return $shadow ?: null;
        }

        if ($key === 'backgroundGradient') {
            if (! is_array($value)) {
                return null;
            }

            $from = $value['from'] ?? null;
            $to = $value['to'] ?? null;

            if (! is_string($from) || ! is_string($to)
                || ! $this->isSafeColorOrToken($from) || ! $this->isSafeColorOrToken($to)) {
                return null;
            }

            $direction = in_array($value['direction'] ?? 'to-bottom', ['to-top', 'to-bottom', 'to-left', 'to-right'], true)
                ? $value['direction']
                : 'to-bottom';

            return ['from' => $from, 'to' => $to, 'direction' => $direction];
        }

        if (in_array($key, ['color', 'backgroundColor', 'borderColor', 'overlayColor'], true)) {
            return is_string($value) && $this->isSafeColorOrToken($value) ? $value : null;
        }

        if (in_array($key, ['backgroundImage'], true)) {
            if (! is_string($value)) {
                return null;
            }

            $isBuiltin = (bool) preg_match('/^builtin:[a-z0-9-]{1,40}$/', $value);
            $isPath = (bool) preg_match('#^[A-Za-z0-9][A-Za-z0-9/_.-]*\.(jpg|jpeg|png|webp|gif|svg|avif)$#i', $value)
                && ! str_contains($value, '..');

            return ($isBuiltin || $isPath) ? $value : null;
        }

        if (in_array($key, ['textAlign', 'justifyContent', 'alignItems', 'flexDirection', 'display', 'position', 'overflow', 'objectFit', 'backgroundSize', 'borderStyle', 'blendMode'], true)) {
            return is_string($value) && preg_match('/^[a-z-]{1,20}$/', $value) ? $value : null;
        }

        if ($key === 'fontWeight') {
            return is_string($value) && preg_match('/^[1-9]00$/', $value) ? $value : null;
        }

        if (is_numeric($value)) {
            return (float) $value;
        }

        return null;
    }

    private function isSafeColorOrToken(string $value): bool
    {
        if (preg_match('/^#([0-9a-fA-F]{3}|[0-9a-fA-F]{4}|[0-9a-fA-F]{6}|[0-9a-fA-F]{8})$/', $value)) {
            return true;
        }

        if (preg_match('/^(rgb|rgba|hsl|hsla)\(\s*[0-9.,%\s\/deg]+\)$/i', $value)) {
            return true;
        }

        // A theme token name.
        return (bool) preg_match('/^[a-zA-Z][a-zA-Z0-9_-]{0,30}$/', $value);
    }

    // ── Animation / effects / theme ────────────────────────────────────────

    private function validateAnimation(mixed $animation): array
    {
        if (! is_array($animation)) {
            return [];
        }

        $registry = $this->animations;
        $out = [];

        foreach (self::ANIMATION_TRIGGERS as $trigger) {
            $config = $animation[$trigger] ?? null;

            if (! is_array($config) || ($config['enabled'] ?? true) === false) {
                continue;
            }

            $type = $config['type'] ?? null;

            if (! is_string($type) || ! $registry->has($type)) {
                continue;
            }

            $entry = ['type' => $type];

            if (isset($config['duration']) && is_numeric($config['duration'])) {
                $entry['duration'] = (int) max(0, min(20000, (float) $config['duration']));
            }

            if (isset($config['delay']) && is_numeric($config['delay'])) {
                $entry['delay'] = (int) max(0, min(10000, (float) $config['delay']));
            }

            if (isset($config['intensity']) && is_numeric($config['intensity'])) {
                $entry['intensity'] = (float) max(0, min(3, (float) $config['intensity']));
            }

            if (is_string($config['easing'] ?? null) && in_array($config['easing'], self::EASINGS, true)) {
                $entry['easing'] = $config['easing'];
            }

            if (($config['repeat'] ?? null) === 'loop') {
                $entry['repeat'] = 'loop';
            }

            $out[$trigger] = $entry;
        }

        return $out;
    }

    private function validateEffects(mixed $effects): array
    {
        if (! is_array($effects)) {
            return [];
        }

        $out = [];

        foreach ($effects as $effect) {
            $type = is_array($effect) ? ($effect['type'] ?? null) : null;

            if (! is_string($type) || ! $this->effects->has($type)) {
                continue;
            }

            $out[] = [
                'type' => $type,
                'props' => is_array($effect['props'] ?? null)
                    ? array_filter($effect['props'], fn ($v) => is_scalar($v))
                    : [],
            ];
        }

        return $out;
    }

    private function validateTheme(mixed $theme): array
    {
        if (! is_array($theme)) {
            return [];
        }

        $out = [];

        $colors = $theme['colors'] ?? null;
        if (is_array($colors)) {
            foreach ($colors as $token => $value) {
                if (is_string($token) && preg_match('/^[a-zA-Z][a-zA-Z0-9_-]{0,30}$/', $token)
                    && is_string($value) && $this->isSafeColorOrToken($value)) {
                    $out['colors'][$token] = $value;
                }
            }
        }

        $fonts = $theme['typography'] ?? null;
        if (is_array($fonts)) {
            foreach (['headingFont', 'bodyFont'] as $key) {
                $value = $fonts[$key] ?? null;

                // Font names are interpolated into a Google Fonts URL.
                if (is_string($value) && preg_match("/^[A-Za-z0-9 '\-]{1,60}$/", $value)) {
                    $out['typography'][$key] = $value;
                }
            }
        }

        if (isset($theme['radius']) && is_numeric($theme['radius'])) {
            $out['radius'] = (int) max(0, min(80, (float) $theme['radius']));
        }

        return $out;
    }

    private function validateOverlays(mixed $overlays): array
    {
        if (! is_array($overlays)) {
            return [];
        }

        $registry = $this->overlays;
        $out = [];
        $seen = [];

        foreach ($overlays as $overlay) {
            if (! is_array($overlay)) {
                continue;
            }

            $type = $overlay['type'] ?? null;

            if (! is_string($type) || ! $registry->has($type)) {
                continue;
            }

            $id = $this->uniqueId($overlay['id'] ?? null, $seen);

            // Overlay props are declared per overlay in the registry.
            $allowed = [];
            foreach ($registry->get($type)['inspector'] ?? [] as $field) {
                if (is_array($field) && isset($field['name'])) {
                    $allowed[] = $field['name'];
                }
            }

            $props = [];
            foreach ((array) ($overlay['props'] ?? []) as $key => $value) {
                if (! is_string($key) || ! in_array($key, $allowed, true)) {
                    continue;
                }

                $clean = $this->sanitizeValue($value);

                if ($clean !== null) {
                    $props[$key] = $clean;
                }
            }

            $out[] = [
                'id' => $id,
                'type' => $type,
                'enabled' => ($overlay['enabled'] ?? true) !== false,
                'mobile' => ($overlay['mobile'] ?? true) !== false,
                'props' => $props,
                'styles' => $this->validateStyles($overlay['styles'] ?? [], false),
            ];
        }

        return $out;
    }

    // ── Helpers ────────────────────────────────────────────────────────────

    private function uniqueId(mixed $id, array &$seen): string
    {
        $candidate = is_string($id) && preg_match('/^[A-Za-z0-9_-]{1,64}$/', $id)
            ? $id
            : 'n_'.Str::lower(Str::random(8));

        while (isset($seen[$candidate])) {
            $candidate = 'n_'.Str::lower(Str::random(8));
        }

        $seen[$candidate] = true;

        return $candidate;
    }

    /**
     * Scalars and shallow arrays only; long strings are truncated.
     */
    private function sanitizeValue(mixed $value): mixed
    {
        if (is_bool($value) || is_int($value) || is_float($value)) {
            return $value;
        }

        if (is_string($value)) {
            $value = trim($value);

            return $value === '' ? null : Str::limit($value, 5000, '');
        }

        if (is_array($value)) {
            $out = [];

            foreach ($value as $k => $v) {
                $clean = $this->sanitizeValue($v);

                if ($clean !== null) {
                    $out[$k] = $clean;
                }
            }

            return $out ?: null;
        }

        return null;
    }
}
