<?php

namespace App\Services;

use App\Models\Template;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;

class TemplateService
{
    private const CACHE_TTL = 3600; // 1 hour

    public function all(): Collection
    {
        return Cache::remember('template_registry', self::CACHE_TTL, fn () => Template::active()->get()
        );
    }

    public function find(string $key): ?Template
    {
        return $this->all()->firstWhere('key', $key);
    }

    public function sectionView(string $templateKey, string $sectionKey): string
    {
        $view = "templates.{$templateKey}.sections.{$sectionKey}";

        if (view()->exists($view)) {
            return $view;
        }

        $default = "templates.default.sections.{$sectionKey}";

        return view()->exists($default) ? $default : 'templates.default.sections._fallback';
    }

    public function layoutView(string $templateKey): string
    {
        $view = "templates.{$templateKey}.layout";

        return view()->exists($view) ? $view : 'templates.default.layout';
    }

    /**
     * Canonical, ordered section keys (spec §18).
     */
    public function defaultSections(): array
    {
        return array_keys(config('ngundang.sections', []));
    }

    /**
     * Human labels for the canonical sections, keyed by section key.
     */
    public function sectionLabels(): array
    {
        return config('ngundang.sections', []);
    }

    public function sectionLabel(string $key): string
    {
        return $this->sectionLabels()[$key] ?? ucfirst(str_replace('_', ' ', $key));
    }

    /**
     * Editable text fields, keyed by section then field.
     */
    public function sectionFields(): array
    {
        return config('ngundang.section_fields', []);
    }

    /**
     * Resolve a section's text values against the registry defaults.
     *
     * @param  array<string, mixed>  $settings  Stored section settings.
     * @return array<string, string>
     */
    public function sectionText(string $sectionKey, array $settings = []): array
    {
        $fields = $this->sectionFields()[$sectionKey] ?? [];
        $stored = is_array($settings['text'] ?? null) ? $settings['text'] : [];

        $text = [];
        foreach ($fields as $field => $meta) {
            $value = $stored[$field] ?? null;
            $text[$field] = (is_string($value) && trim($value) !== '')
                ? $value
                : ($meta['default'] ?? '');
        }

        return $text;
    }

    /**
     * Default text for every section — used by the builder to seed its state.
     *
     * @return array<string, array<string, string>>
     */
    public function defaultText(): array
    {
        $out = [];
        foreach (array_keys($this->sectionFields()) as $sectionKey) {
            $out[$sectionKey] = $this->sectionText($sectionKey);
        }

        return $out;
    }

    /**
     * Keep only known sections/fields whose value is a non-empty string and
     * differs from the registry default. Stored overrides therefore stay
     * minimal and cannot carry injected keys.
     *
     * @param  array<string, mixed>  $input
     * @return array<string, array<string, string>>
     */
    public function filterTextOverrides(array $input): array
    {
        $fields = $this->sectionFields();
        $out = [];

        foreach ($input as $sectionKey => $values) {
            if (! isset($fields[$sectionKey]) || ! is_array($values)) {
                continue;
            }

            foreach ($values as $field => $value) {
                if (! isset($fields[$sectionKey][$field]) || ! is_string($value)) {
                    continue;
                }

                $value = trim($value);

                if ($value === '' || $value === ($fields[$sectionKey][$field]['default'] ?? '')) {
                    continue;
                }

                $out[$sectionKey][$field] = $value;
            }
        }

        return $out;
    }

    /**
     * A fully-enabled layout using the canonical order.
     */
    public function defaultLayout(): array
    {
        return array_map(
            fn ($key) => ['key' => $key, 'enabled' => true, 'title' => null],
            $this->defaultSections()
        );
    }

    /**
     * Sanitise a stored layout: keep only canonical keys, drop duplicates,
     * preserve the saved order, and append any canonical section that is
     * missing so a template can never lose a section entirely.
     */
    public function normalizeLayout(?array $layout): array
    {
        $known = $this->defaultSections();
        $seen = [];

        foreach ((array) $layout as $entry) {
            $key = is_array($entry) ? ($entry['key'] ?? null) : $entry;

            if (! is_string($key) || ! in_array($key, $known, true) || isset($seen[$key])) {
                continue;
            }

            $seen[$key] = [
                'key' => $key,
                'enabled' => is_array($entry) ? (bool) ($entry['enabled'] ?? true) : true,
                'title' => (is_array($entry) && ! empty($entry['title'])) ? (string) $entry['title'] : null,
                'settings' => (is_array($entry) && is_array($entry['settings'] ?? null)) ? $entry['settings'] : [],
            ];
        }

        $ordered = array_values($seen);

        foreach ($known as $key) {
            if (! isset($seen[$key])) {
                $ordered[] = ['key' => $key, 'enabled' => true, 'title' => null, 'settings' => []];
            }
        }

        return $ordered;
    }

    /**
     * The effective layout for a template — always the full canonical set.
     */
    public function layoutForTemplate(?Template $template): array
    {
        $layout = $this->normalizeLayout($template?->default_sections);

        return $layout ?: $this->defaultLayout();
    }

    public function forgetCache(): void
    {
        Cache::forget('template_registry');
    }
}
