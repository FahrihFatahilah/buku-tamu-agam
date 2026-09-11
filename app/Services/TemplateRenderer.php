<?php

namespace App\Services;

use App\Models\Guest;
use App\Models\Wedding;
use App\Services\QrCodeService;
use Illuminate\Support\Collection;

class TemplateRenderer
{
    public function __construct(private TemplateService $templateService) {}

    /**
     * Build the view data array for rendering an invitation.
     */
    public function buildViewData(
        Wedding $wedding,
        ?Guest $guest,
        ?Collection $hiddenGiftIds = null,
        ?Collection $hiddenEventIds = null,
    ): array {
        $templateKey = $wedding->template?->key ?? 'minang-elegance';

        $events = $wedding->events()
            ->where('is_public', true)
            ->when($hiddenEventIds?->isNotEmpty(), fn($q) => $q->whereNotIn('id', $hiddenEventIds))
            ->get();

        $giftMethods = $wedding->giftMethods()
            ->active()
            ->when($hiddenGiftIds?->isNotEmpty(), fn($q) => $q->whereNotIn('id', $hiddenGiftIds))
            ->get();

        return [
            'wedding'         => $wedding,
            'guest'           => $guest,
            'sections'        => $wedding->sections()->where('is_enabled', true)->orderBy('sort_order')->get(),
            'events'          => $events,
            'giftMethods'     => $giftMethods,
            'activePlaylist'  => $wedding->activePlaylist()->with('items')->first(),
            'media'           => $wedding->media()->orderBy('sort_order')->get(),
            'templateKey'     => $templateKey,
            'templateService' => $this->templateService,
            'qrCode'          => $guest ? app(QrCodeService::class)->generate($guest, 200) : null,
        ];
    }

    public function layoutView(string $templateKey): string
    {
        return $this->templateService->layoutView($templateKey);
    }
}
