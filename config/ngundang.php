<?php

return [

    'platform_domain' => env('NGUNDANG_PLATFORM_DOMAIN', 'ngundang.com'),

    'app_name' => env('APP_NAME', 'Ngundang'),

    /*
    |--------------------------------------------------------------------------
    | Canonical Sections
    |--------------------------------------------------------------------------
    |
    | The ordered set of sections an invitation may contain (spec §18).
    | This is the single source of truth: the template builder, the wedding
    | section bootstrap, and the renderer all read from here.
    |
    */

    'sections' => [
        'opening' => 'Opening',
        'hero' => 'Hero',
        'couple' => 'Mempelai',
        'quote' => 'Kutipan',
        'countdown' => 'Countdown',
        'event' => 'Acara',
        'venue' => 'Lokasi',
        'maps' => 'Maps',
        'love_story' => 'Kisah Cinta',
        'gallery' => 'Galeri',
        'video' => 'Video',
        'rsvp' => 'RSVP',
        'guest_book' => 'Buku Tamu',
        'gift' => 'Hadiah',
        'timeline' => 'Timeline',
        'closing' => 'Penutup',
    ],

    /*
    |--------------------------------------------------------------------------
    | Palettes
    |--------------------------------------------------------------------------
    |
    | Preset colour sets offered in the template builder. A template stores its
    | chosen palette in default_settings.palette; the renderer maps it to CSS
    | custom properties so builder-created templates are visually distinct.
    |
    */

    'palettes' => [
        'minang' => [
            'label' => 'Deep Maroon · Cream · Gold',
            'primary' => '#7C3238',
            'secondary' => '#F5F0E8',
            'accent' => '#B8960C',
            'dark' => '#2C1810',
        ],
        'luxury' => [
            'label' => 'Black · White · Gold',
            'primary' => '#1A1A1A',
            'secondary' => '#FAFAFA',
            'accent' => '#C9A96E',
            'dark' => '#111111',
        ],
        'floral' => [
            'label' => 'Rose · Blush · Sage',
            'primary' => '#B5606A',
            'secondary' => '#F9F0F0',
            'accent' => '#7A9E7E',
            'dark' => '#4A2030',
        ],
        'islamic' => [
            'label' => 'Green · Cream · Gold',
            'primary' => '#1A4A2E',
            'secondary' => '#FDF8F0',
            'accent' => '#C9A84C',
            'dark' => '#14321F',
        ],
        'nusantara' => [
            'label' => 'Brown · Cream · Gold',
            'primary' => '#4A2C0A',
            'secondary' => '#FDF5E4',
            'accent' => '#C9A84C',
            'dark' => '#2A1806',
        ],
        'minimal' => [
            'label' => 'Black · White · Grey',
            'primary' => '#111111',
            'secondary' => '#FFFFFF',
            'accent' => '#888888',
            'dark' => '#1A1A1A',
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Fonts
    |--------------------------------------------------------------------------
    |
    | Curated Google Fonts offered in the builder. Curated rather than freeform
    | because the font name is interpolated into a Google Fonts URL.
    |
    */

    'fonts' => [
        'display' => [
            'Playfair Display',
            'Cormorant Garamond',
            'EB Garamond',
            'Lora',
            'Libre Baskerville',
            'DM Serif Display',
        ],
        'body' => [
            'Lato',
            'Inter',
            'Open Sans',
            'Nunito',
            'DM Sans',
            'Source Sans 3',
        ],
    ],

];
