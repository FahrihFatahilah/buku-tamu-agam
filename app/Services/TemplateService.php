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
        return Cache::remember('template_registry', self::CACHE_TTL, fn() =>
            Template::active()->get()
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
     * Section keys from the product spec (prompt.md §18).
     */
    public function defaultSections(): array
    {
        return [
            'opening', 'hero', 'couple', 'quote', 'countdown',
            'event', 'venue', 'maps', 'love_story', 'gallery',
            'video', 'rsvp', 'guest_book', 'gift', 'timeline', 'closing',
        ];
    }

    public function forgetCache(): void
    {
        Cache::forget('template_registry');
    }
}
