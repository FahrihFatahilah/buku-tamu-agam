<?php

namespace App\Services;

use App\Models\GiftMethod;
use App\Models\Template;
use App\Models\Wedding;
use App\Models\WeddingEvent;
use App\Models\WeddingSection;
use Illuminate\Support\Collection;

/**
 * Renders a template against sample content so the builder can show a live
 * preview without needing a real wedding attached to the template.
 */
class TemplatePreviewService
{
    public function __construct(private TemplateService $templateService) {}

    /**
     * @param  array<int, string>  $order  Section keys in display order.
     * @param  array<int, string>  $disabled  Section keys to treat as disabled.
     * @param  array<string, mixed>  $text  Unsaved text overrides, keyed by section then field.
     */
    public function build(
        string $paletteKey,
        string $fontDisplay,
        string $fontBody,
        array $order,
        array $disabled,
        ?Template $template = null,
        array $text = [],
    ): array {
        $palettes = config('ngundang.palettes', []);
        $palette = $palettes[$paletteKey] ?? (reset($palettes) ?: []);
        unset($palette['label']);

        // A throwaway template instance carrying the editor's unsaved settings.
        $preview = $template ? clone $template : new Template;
        $settings = $preview->default_settings ?? [];
        $settings['palette'] = $palette;
        $settings['fonts'] = ['display' => $fontDisplay, 'body' => $fontBody];
        $preview->default_settings = $settings;

        $wedding = $this->wedding();
        $wedding->setRelation('template', $preview);

        return [
            'wedding' => $wedding,
            'guest' => null,
            'sections' => $this->sections($order, $disabled, $text),
            'events' => $this->events(),
            'giftMethods' => $this->giftMethods(),
            'activePlaylist' => null,
            'media' => collect(),
            'templateKey' => $template?->key ?? 'default',
            'templateService' => $this->templateService,
            'qrCode' => null,
        ];
    }

    private function wedding(): Wedding
    {
        return new Wedding([
            'public_id' => 'INV-PREV01',
            'short_id' => 'preview',
            'slug' => 'contoh-undangan',
            'title' => 'Contoh Undangan',
            'groom_name' => 'Andi Pratama',
            'bride_name' => 'Sari Dewi',
            'groom_nickname' => 'Andi',
            'bride_nickname' => 'Sari',
            'groom_father' => 'Bapak Pratama',
            'groom_mother' => 'Ibu Pratama',
            'bride_father' => 'Bapak Dewi',
            'bride_mother' => 'Ibu Dewi',
            'description' => 'Dengan memohon rahmat dan ridho Allah SWT, kami mengundang Bapak/Ibu/Saudara/i untuk hadir dalam acara pernikahan kami.',
            'quote' => 'Dan di antara tanda-tanda kekuasaan-Nya ialah Dia menciptakan untukmu pasangan dari jenismu sendiri, supaya kamu merasa tenteram kepadanya.',
            'settings' => ['quote_source' => 'QS. Ar-Rum: 21'],
            'date' => now()->addMonths(2)->toDateString(),
            'venue' => 'Gedung Serbaguna Minang Permai',
            'address' => 'Jl. Minang Permai No. 1, Padang, Sumatera Barat',
            'maps_url' => 'https://www.google.com/maps/search/?api=1&query=Padang',
            'status' => 'published',
        ]);
    }

    private function sections(array $order, array $disabled, array $text = []): Collection
    {
        $canonical = $this->templateService->defaultSections();

        $ordered = [];
        foreach ($order as $key) {
            if (in_array($key, $canonical, true) && ! in_array($key, $ordered, true)) {
                $ordered[] = $key;
            }
        }
        foreach ($canonical as $key) {
            if (! in_array($key, $ordered, true)) {
                $ordered[] = $key;
            }
        }

        return collect($ordered)->map(function ($key, $i) use ($disabled, $text) {
            $settings = $this->sampleSettings($key);

            // Unsaved builder edits take precedence over the sample/defaults.
            if (! empty($text[$key]) && is_array($text[$key])) {
                $settings['text'] = $text[$key];
            }

            return new WeddingSection([
                'section_key' => $key,
                'title' => null,
                'is_enabled' => ! in_array($key, $disabled, true),
                'sort_order' => $i,
                'settings' => $settings,
            ]);
        });
    }

    /**
     * Sample per-section settings so list-driven sections show something real.
     */
    private function sampleSettings(string $key): array
    {
        return match ($key) {
            'love_story' => [
                'stories' => [
                    ['year' => '2019', 'title' => 'Pertama Bertemu', 'description' => 'Kami dipertemukan di sebuah acara kampus di Padang.'],
                    ['year' => '2023', 'title' => 'Melamar', 'description' => 'Dengan restu keluarga, kami memutuskan untuk melangkah ke jenjang yang lebih serius.'],
                ],
            ],
            'timeline' => [
                'items' => [
                    ['time' => '08.00', 'title' => 'Akad Nikah', 'description' => 'Masjid Al-Ikhlas, Padang'],
                    ['time' => '11.00', 'title' => 'Resepsi', 'description' => 'Gedung Serbaguna Minang Permai'],
                ],
            ],
            default => [],
        };
    }

    private function events(): Collection
    {
        return collect([
            new WeddingEvent([
                'name' => 'Akad Nikah',
                'type' => 'akad',
                'starts_at' => now()->addMonths(2)->setTime(8, 0),
                'ends_at' => now()->addMonths(2)->setTime(10, 0),
                'venue' => 'Masjid Al-Ikhlas',
                'address' => 'Jl. Minang Permai No. 1, Padang',
                'dress_code' => 'Formal - Merah Maroon',
                'is_public' => true,
            ]),
            new WeddingEvent([
                'name' => 'Resepsi',
                'type' => 'reception',
                'starts_at' => now()->addMonths(2)->setTime(11, 0),
                'ends_at' => now()->addMonths(2)->setTime(15, 0),
                'venue' => 'Gedung Serbaguna Minang Permai',
                'address' => 'Jl. Minang Permai No. 1, Padang, Sumatera Barat',
                'dress_code' => 'Formal - Merah Maroon',
                'is_public' => true,
            ]),
        ]);
    }

    private function giftMethods(): Collection
    {
        return collect([
            new GiftMethod([
                'type' => 'bank_transfer',
                'label' => 'BCA',
                'bank_name' => 'BCA',
                'account_number' => '1234567890',
                'account_holder' => 'Sari Dewi',
                'is_active' => true,
            ]),
            new GiftMethod([
                'type' => 'qris',
                'label' => 'QRIS',
                'merchant_name' => 'Andi & Sari Wedding',
                'is_active' => true,
            ]),
        ]);
    }
}
