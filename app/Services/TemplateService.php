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
        return view()->exists($view) ? $view : "templates.default.sections.{$sectionKey}";
    }

    public function layoutView(string $templateKey): string
    {
        $view = "templates.{$templateKey}.layout";
        return view()->exists($view) ? $view : 'templates.default.layout';
    }

    public function defaultSections(): array
    {
        return [
            'opening', 'hero', 'couple', 'quote', 'countdown',
            'event', 'venue', 'love_story', 'gallery',
            'video', 'rsvp', 'guest_book', 'gift', 'timeline', 'closing',
        ];
    }

    public function forgetCache(): void
    {
        Cache::forget('template_registry');
    }
}
