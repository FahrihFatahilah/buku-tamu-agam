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
            ];
        }

        $ordered = array_values($seen);

        foreach ($known as $key) {
            if (! isset($seen[$key])) {
                $ordered[] = ['key' => $key, 'enabled' => true, 'title' => null];
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
