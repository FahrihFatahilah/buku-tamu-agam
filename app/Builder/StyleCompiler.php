<?php

namespace App\Builder;

/**
 * Compiles a document's per-breakpoint styles into a single stylesheet.
 *
 * Two reasons this is server-side and emitted once for the whole document:
 *  - The public page must stay lightweight: one <style> block, no JS, no
 *    duplicated markup per breakpoint.
 *  - Styling is the main injection surface, so every property name and value
 *    is validated against a whitelist here. Nothing arbitrary is emitted.
 */
class StyleCompiler
{
    public const BREAKPOINTS = [
        'desktop' => null,
        'tablet' => 1024,
        'mobile' => 640,
    ];

    /** Style keys that combine into a single `transform` declaration. */
    private const TRANSFORM_KEYS = ['rotation', 'scale', 'flipH', 'flipV', 'translateX', 'translateY'];

    private const ALIGNMENTS = ['left', 'center', 'right', 'justify'];

    private const JUSTIFY = ['flex-start', 'center', 'flex-end', 'space-between', 'space-around', 'space-evenly'];

    private const ALIGN_ITEMS = ['flex-start', 'center', 'flex-end', 'stretch', 'baseline'];

    private const FLEX_DIRECTION = ['row', 'column', 'row-reverse', 'column-reverse'];

    private const DISPLAY = ['block', 'flex', 'grid', 'inline-block', 'inline-flex', 'none'];

    private const POSITION = ['static', 'relative', 'absolute', 'fixed', 'sticky'];

    private const BLEND_MODES = ['normal', 'multiply', 'screen', 'overlay', 'darken', 'lighten', 'color-dodge', 'color-burn', 'soft-light', 'hard-light', 'difference', 'exclusion', 'hue', 'saturation', 'color', 'luminosity'];

    private const OBJECT_FIT = ['cover', 'contain', 'fill', 'none', 'scale-down'];

    private const BORDER_STYLES = ['solid', 'dashed', 'dotted', 'double'];

    private const OVERFLOW = ['visible', 'hidden', 'auto', 'scroll'];

    private const BACKGROUND_SIZES = ['cover', 'contain', 'auto', 'repeat'];

    public function __construct(private array $theme = []) {}

    /**
     * The complete stylesheet for a document.
     */
    public function compile(array $document): string
    {
        $theme = is_array($document['theme'] ?? null) ? $document['theme'] : $this->theme;

        $base = [];
        $tablet = [];
        $mobile = [];

        foreach ($this->flattenNodes($document['nodes'] ?? []) as $node) {
            $id = $node['id'] ?? null;
            if (! is_string($id) || $id === '') {
                continue;
            }

            $styles = is_array($node['styles'] ?? null) ? $node['styles'] : [];
            $isDecoration = ($node['type'] ?? null) === 'decoration';

            foreach (['desktop', 'tablet', 'mobile'] as $breakpoint) {
                $declarations = $this->declarations($styles[$breakpoint] ?? [], $isDecoration, $theme);

                if (! $declarations) {
                    continue;
                }

                $rule = sprintf('[data-node-id="%s"]{%s}', $this->escapeId($id), implode(';', $declarations));

                match ($breakpoint) {
                    'desktop' => $base[] = $rule,
                    'tablet' => $tablet[] = $rule,
                    'mobile' => $mobile[] = $rule,
                };
            }
        }

        foreach ($document['overlays'] ?? [] as $overlay) {
            if (! is_array($overlay) || empty($overlay['id'])) {
                continue;
            }

            $styles = is_array($overlay['styles'] ?? null) ? $overlay['styles'] : [];

            foreach (['desktop', 'tablet', 'mobile'] as $breakpoint) {
                $declarations = $this->declarations($styles[$breakpoint] ?? [], false, $theme);

                if (! $declarations) {
                    continue;
                }

                $rule = sprintf('[data-overlay-id="%s"]{%s}', $this->escapeId($overlay['id']), implode(';', $declarations));

                match ($breakpoint) {
                    'desktop' => $base[] = $rule,
                    'tablet' => $tablet[] = $rule,
                    'mobile' => $mobile[] = $rule,
                };
            }
        }

        $css = $this->themeVariables($theme);

        $css .= implode('', $base);

        if ($tablet) {
            $css .= '@media (max-width:1024px){'.implode('', $tablet).'}';
        }

        if ($mobile) {
            $css .= '@media (max-width:640px){'.implode('', $mobile).'}';
        }

        return $css;
    }

    /**
     * Theme tokens exposed as CSS custom properties, so widgets and style
     * values can reference the global theme instead of hardcoding colours.
     */
    public function themeVariables(array $theme): string
    {
        $colors = is_array($theme['colors'] ?? null) ? $theme['colors'] : [];
        $typography = is_array($theme['typography'] ?? null) ? $theme['typography'] : [];

        $vars = [];

        foreach ($colors as $token => $value) {
            if (is_string($value) && $this->isSafeColor($value)) {
                $vars['--n-'.$this->escapeToken($token)] = $value;
            }
        }

        $display = $typography['headingFont'] ?? null;
        $body = $typography['bodyFont'] ?? null;

        if (is_string($display) && $this->isSafeFontName($display)) {
            $vars['--n-font-display'] = "'{$display}', serif";
        }

        if (is_string($body) && $this->isSafeFontName($body)) {
            $vars['--n-font-body'] = "'{$body}', sans-serif";
        }

        if (isset($theme['radius']) && is_numeric($theme['radius'])) {
            $vars['--n-radius'] = ((int) $theme['radius']).'px';
        }

        if (empty($vars)) {
            return '';
        }

        $declarations = [];
        foreach ($vars as $name => $value) {
            $declarations[] = "{$name}:{$value}";
        }

        return ':root{'.implode(';', $declarations).'}';
    }

    // ── Internals ──────────────────────────────────────────────────────────

    /**
     * @return array<int, string> "property:value" pairs
     */
    private function declarations(array $styles, bool $isDecoration, array $theme): array
    {
        if (! $styles) {
            return [];
        }

        $out = [];
        $transforms = [];

        // Decorations are absolutely positioned objects.
        if ($isDecoration) {
            $out[] = 'position:absolute';
        }

        foreach ($styles as $key => $value) {
            if (! is_string($key)) {
                continue;
            }

            if (in_array($key, self::TRANSFORM_KEYS, true)) {
                if ($transform = $this->transformPart($key, $value)) {
                    $transforms[] = $transform;
                }

                continue;
            }

            if ($declaration = $this->declaration($key, $value, $theme)) {
                $out[] = $declaration;
            }
        }

        if ($transforms) {
            $out[] = 'transform:'.implode(' ', $transforms);
        }

        return $out;
    }

    private function declaration(string $key, mixed $value, array $theme): ?string
    {
        return match ($key) {
            'fontSize' => $this->px('font-size', $value, 8, 200),
            'letterSpacing' => $this->px('letter-spacing', $value, -10, 40),
            'lineHeight' => $this->lineHeight($value),
            'fontWeight' => $this->keywordNumber('font-weight', $value, ['300', '400', '500', '600', '700', '800', '900']),
            'textAlign' => $this->keyword('text-align', $value, self::ALIGNMENTS),
            'color' => $this->color('color', $value, $theme),
            'backgroundColor' => $this->color('background-color', $value, $theme),
            'borderColor' => $this->color('border-color', $value, $theme),
            'backgroundImage' => $this->backgroundImage($value),
            'backgroundGradient' => $this->gradient($value, $theme),
            'backgroundSize' => $this->backgroundSize($value),
            'padding' => $this->spacing('padding', $value),
            'margin' => $this->spacing('margin', $value),
            'borderWidth' => $this->borderWidth($value),
            'borderStyle' => $this->keyword('border-style', $value, self::BORDER_STYLES),
            'borderRadius' => $this->px('border-radius', $value, 0, 400),
            'boxShadow' => $this->shadow($value, $theme),
            'opacity' => $this->percent('opacity', $value),
            'width' => $this->px('width', $value, 0, 4000),
            'height' => $this->px('height', $value, 0, 4000),
            'maxWidth' => $this->px('max-width', $value, 0, 4000),
            'minHeight' => $this->px('min-height', $value, 0, 4000),
            'gap' => $this->px('gap', $value, 0, 400),
            'x' => $this->px('left', $value, -4000, 4000),
            'y' => $this->px('top', $value, -4000, 8000),
            'zIndex' => $this->integer('z-index', $value, -100, 9999),
            'blur' => $this->filterBlur($value),
            'blendMode' => $this->keyword('mix-blend-mode', $value, self::BLEND_MODES),
            'justifyContent' => $this->keyword('justify-content', $value, self::JUSTIFY),
            'alignItems' => $this->keyword('align-items', $value, self::ALIGN_ITEMS),
            'flexDirection' => $this->keyword('flex-direction', $value, self::FLEX_DIRECTION),
            'display' => $this->keyword('display', $value, self::DISPLAY),
            'position' => $this->keyword('position', $value, self::POSITION),
            'overflow' => $this->keyword('overflow', $value, self::OVERFLOW),
            'objectFit' => $this->keyword('object-fit', $value, self::OBJECT_FIT),
            'visible' => $this->visibility($value),
            'overlayColor' => $this->overlayVar('--n-overlay-color', $value, $theme),
            'overlayOpacity' => $this->overlayOpacityVar($value),
            default => null,
        };
    }

    private function transformPart(string $key, mixed $value): ?string
    {
        if (! is_numeric($value)) {
            // Booleans for flips.
            if ($key === 'flipH' && $value) {
                return 'scaleX(-1)';
            }
            if ($key === 'flipV' && $value) {
                return 'scaleY(-1)';
            }

            return null;
        }

        $number = (float) $value;

        return match ($key) {
            'rotation' => $number !== 0.0 ? 'rotate('.round($number, 2).'deg)' : null,
            'scale' => $number !== 100.0 ? 'scale('.round(max(10, min(300, $number)) / 100, 4).')' : null,
            'translateX' => 'translateX('.round($number, 2).'px)',
            'translateY' => 'translateY('.round($number, 2).'px)',
            default => null,
        };
    }

    private function px(string $property, mixed $value, float $min, float $max): ?string
    {
        if (! is_numeric($value)) {
            return null;
        }

        $number = (float) $value;

        if ($number < $min || $number > $max) {
            return null;
        }

        // Round to avoid long floats in the output.
        $formatted = rtrim(rtrim(number_format($number, 2, '.', ''), '0'), '.');

        return "{$property}:{$formatted}px";
    }

    private function integer(string $property, mixed $value, int $min, int $max): ?string
    {
        if (! is_numeric($value)) {
            return null;
        }

        $number = (int) $value;

        if ($number < $min || $number > $max) {
            return null;
        }

        return "{$property}:{$number}";
    }

    private function percent(string $property, mixed $value): ?string
    {
        if (! is_numeric($value)) {
            return null;
        }

        return sprintf('%s:%s', $property, round(max(0, min(100, (float) $value)) / 100, 3));
    }

    private function lineHeight(mixed $value): ?string
    {
        if (! is_numeric($value)) {
            return null;
        }

        $number = (float) $value;

        // Authored as a percentage (100 = normal).
        return 'line-height:'.round(max(80, min(300, $number)) / 100, 3);
    }

    private function keywordNumber(string $property, mixed $value, array $allowed): ?string
    {
        $value = (string) $value;

        return in_array($value, $allowed, true) ? "{$property}:{$value}" : null;
    }

    private function keyword(string $property, mixed $value, array $allowed): ?string
    {
        if (! is_string($value)) {
            return null;
        }

        return in_array($value, $allowed, true) ? "{$property}:{$value}" : null;
    }

    private function visibility(mixed $value): ?string
    {
        return $value === false || $value === 0 || $value === '0' ? 'display:none' : null;
    }

    private function filterBlur(mixed $value): ?string
    {
        if (! is_numeric($value)) {
            return null;
        }

        $number = max(0, min(40, (float) $value));

        return $number > 0 ? 'filter:blur('.round($number, 2).'px)' : null;
    }

    private function color(string $property, mixed $value, array $theme): ?string
    {
        if (! is_string($value) || $value === '') {
            return null;
        }

        // A theme token name resolves to its custom property.
        if ($this->isToken($value, $theme)) {
            return "{$property}:var(--n-".$this->escapeToken($value).')';
        }

        return $this->isSafeColor($value) ? "{$property}:{$value}" : null;
    }

    private function overlayVar(string $var, mixed $value, array $theme): ?string
    {
        if (! is_string($value) || $value === '') {
            return null;
        }

        if ($this->isToken($value, $theme)) {
            return "{$var}:var(--n-".$this->escapeToken($value).')';
        }

        return $this->isSafeColor($value) ? "{$var}:{$value}" : null;
    }

    private function overlayOpacityVar(mixed $value): ?string
    {
        if (! is_numeric($value)) {
            return null;
        }

        return '--n-overlay-opacity:'.round(max(0, min(100, (float) $value)) / 100, 3);
    }

    private function isToken(string $value, array $theme): bool
    {
        $colors = is_array($theme['colors'] ?? null) ? $theme['colors'] : [];

        return array_key_exists($value, $colors) && ! str_starts_with($value, '#');
    }

    /**
     * Only literal colours, in the forms we author. Blocks url(), expressions,
     * `javascript:` and anything else that could escape the declaration.
     */
    private function isSafeColor(string $value): bool
    {
        $value = trim($value);

        if ($value === '') {
            return false;
        }

        if (preg_match('/^#([0-9a-fA-F]{3}|[0-9a-fA-F]{4}|[0-9a-fA-F]{6}|[0-9a-fA-F]{8})$/', $value)) {
            return true;
        }

        if (preg_match('/^(rgb|rgba|hsl|hsla)\(\s*[0-9.,%\s\/deg]+\)$/i', $value)) {
            return true;
        }

        // Named colours, no functions or punctuation.
        return (bool) preg_match('/^[a-zA-Z]+$/', $value) && strlen($value) <= 30;
    }

    private function isSafeFontName(string $value): bool
    {
        return (bool) preg_match("/^[A-Za-z0-9 '\-]{1,60}$/", $value);
    }

    private function spacing(string $property, mixed $value): ?string
    {
        if (! is_array($value)) {
            return null;
        }

        $parts = [];

        foreach (['top', 'right', 'bottom', 'left'] as $side) {
            $side_value = $value[$side] ?? null;

            if (! is_numeric($side_value)) {
                $parts[] = '0';

                continue;
            }

            $parts[] = rtrim(rtrim(number_format(max(-400, min(800, (float) $side_value)), 2, '.', ''), '0'), '.').'px';
        }

        if ($parts === ['0', '0', '0', '0']) {
            return null;
        }

        return "{$property}:".implode(' ', $parts);
    }

    private function backgroundSize(mixed $value): ?string
    {
        if (! is_string($value) || ! in_array($value, self::BACKGROUND_SIZES, true)) {
            return null;
        }

        return $value === 'repeat' ? 'background-repeat:repeat' : "background-size:{$value}";
    }

    private function borderWidth(mixed $value): ?string
    {
        $width = $this->px('border-width', $value, 0, 40);

        if (! $width) {
            return null;
        }

        // A width without a style does not render, so supply a default.
        return $width.';border-style:solid';
    }

    private function shadow(mixed $value, array $theme): ?string
    {
        if (! is_array($value)) {
            return null;
        }

        $x = is_numeric($value['x'] ?? null) ? (float) $value['x'] : 0;
        $y = is_numeric($value['y'] ?? null) ? (float) $value['y'] : 0;
        $blur = is_numeric($value['blur'] ?? null) ? (float) $value['blur'] : 0;
        $spread = is_numeric($value['spread'] ?? null) ? (float) $value['spread'] : 0;

        if ($x === 0.0 && $y === 0.0 && $blur === 0.0 && $spread === 0.0) {
            return null;
        }

        $color = $this->shadowColor($value['color'] ?? null, $theme);
        $inset = ! empty($value['inset']) ? 'inset ' : '';

        $format = fn (float $n) => rtrim(rtrim(number_format($n, 2, '.', ''), '0'), '.').'px';

        $parts = array_filter([
            $inset.$format($x),
            $format($y),
            $format($blur),
            $format($spread),
            $color,
        ], fn ($part) => $part !== '');

        return 'box-shadow:'.implode(' ', $parts);
    }

    private function shadowColor(mixed $value, array $theme): ?string
    {
        if (is_string($value) && $this->isToken($value, $theme)) {
            return 'var(--n-'.$this->escapeToken($value).')';
        }

        return is_string($value) && $this->isSafeColor($value) ? $value : null;
    }

    private function gradient(mixed $value, array $theme): ?string
    {
        if (! is_array($value)) {
            return null;
        }

        $from = $value['from'] ?? null;
        $to = $value['to'] ?? null;
        $direction = is_string($value['direction'] ?? null) ? $value['direction'] : 'to-bottom';

        if (! in_array($direction, ['to-top', 'to-bottom', 'to-left', 'to-right'], true)) {
            return null;
        }

        $resolve = fn ($color) => is_string($color) && $this->isToken($color, $theme)
            ? 'var(--n-'.$this->escapeToken($color).')'
            : (is_string($color) && $this->isSafeColor($color) ? $color : null);

        $from = $resolve($from);
        $to = $resolve($to);

        if (! $from || ! $to) {
            return null;
        }

        return "background-image:linear-gradient({$direction}, {$from}, {$to})";
    }

    private function backgroundImage(mixed $value): ?string
    {
        if (! is_string($value) || $value === '') {
            return null;
        }

        // Built-in decoration/background assets.
        if (str_starts_with($value, 'builtin:')) {
            $name = substr($value, 8);

            if (! preg_match('/^[a-z0-9-]{1,40}$/', $name)) {
                return null;
            }

            return "background-image:url('/builder/decorations/{$name}.svg')";
        }

        // Storage-relative paths only: no scheme, no traversal, no query.
        if (! preg_match('#^[A-Za-z0-9][A-Za-z0-9/_.-]*\.(jpg|jpeg|png|webp|gif|svg|avif)$#i', $value)) {
            return null;
        }

        if (str_contains($value, '..')) {
            return null;
        }

        return "background-image:url('".$this->escapeUrl($value)."')";
    }

    private function escapeUrl(string $path): string
    {
        return str_replace(["'", '"', '(', ')', '\\', '<', '>'], '', $path);
    }

    private function escapeId(string $id): string
    {
        return preg_replace('/[^A-Za-z0-9_-]/', '', $id) ?? '';
    }

    private function escapeToken(string $token): string
    {
        return preg_replace('/[^A-Za-z0-9_-]/', '', $token) ?? '';
    }

    /**
     * Depth-first walk over every node in the document.
     */
    private function flattenNodes(mixed $nodes): array
    {
        if (! is_array($nodes)) {
            return [];
        }

        $flat = [];

        foreach ($nodes as $node) {
            if (! is_array($node)) {
                continue;
            }

            $flat[] = $node;

            if (is_array($node['children'] ?? null)) {
                $flat = array_merge($flat, $this->flattenNodes($node['children']));
            }
        }

        return $flat;
    }
}
