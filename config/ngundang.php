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
    | Editable Section Text
    |--------------------------------------------------------------------------
    |
    | The text fields the page builder lets you edit inline, per section.
    | `label` is shown in the builder, `default` is what a template starts with.
    | This doubles as the whitelist for persisted overrides — any field not
    | listed here is rejected.
    |
    */

    'section_fields' => [
        'opening' => [
            'eyebrow' => ['label' => 'Label atas', 'default' => 'Undangan Pernikahan'],
            'cta' => ['label' => 'Tombol', 'default' => 'Buka Undangan'],
        ],
        'hero' => [
            'eyebrow' => ['label' => 'Label atas', 'default' => 'The Wedding Of'],
        ],
        'couple' => [
            'eyebrow' => ['label' => 'Label atas', 'default' => 'Mempelai'],
            'heading' => ['label' => 'Judul', 'default' => 'Dengan Penuh Sukacita'],
            'bride_from' => ['label' => 'Keterangan mempelai wanita', 'default' => 'Putri dari'],
            'groom_from' => ['label' => 'Keterangan mempelai pria', 'default' => 'Putra dari'],
        ],
        'countdown' => [
            'eyebrow' => ['label' => 'Label atas', 'default' => 'Menuju Hari Bahagia'],
        ],
        'event' => [
            'eyebrow' => ['label' => 'Label atas', 'default' => 'Rangkaian Acara'],
            'heading' => ['label' => 'Judul', 'default' => 'Jadwal Acara'],
        ],
        'venue' => [
            'eyebrow' => ['label' => 'Label atas', 'default' => 'Lokasi'],
            'heading' => ['label' => 'Judul', 'default' => 'Tempat Acara'],
            'cta' => ['label' => 'Tombol peta', 'default' => 'Buka Google Maps'],
        ],
        'maps' => [
            'cta' => ['label' => 'Tombol peta', 'default' => 'Lihat Lokasi di Google Maps'],
        ],
        'love_story' => [
            'eyebrow' => ['label' => 'Label atas', 'default' => 'Perjalanan Kami'],
            'heading' => ['label' => 'Judul', 'default' => 'Kisah Cinta'],
        ],
        'gallery' => [
            'eyebrow' => ['label' => 'Label atas', 'default' => 'Galeri'],
            'heading' => ['label' => 'Judul', 'default' => 'Momen Bersama'],
        ],
        'video' => [
            'eyebrow' => ['label' => 'Label atas', 'default' => 'Video'],
            'heading' => ['label' => 'Judul', 'default' => 'Momen Bergerak'],
        ],
        'timeline' => [
            'eyebrow' => ['label' => 'Label atas', 'default' => 'Susunan Acara'],
            'heading' => ['label' => 'Judul', 'default' => 'Timeline'],
        ],
        'rsvp' => [
            'eyebrow' => ['label' => 'Label atas', 'default' => 'Konfirmasi Kehadiran'],
            'heading' => ['label' => 'Judul', 'default' => 'RSVP'],
            'submit' => ['label' => 'Tombol kirim', 'default' => 'Kirim Konfirmasi'],
        ],
        'guest_book' => [
            'eyebrow' => ['label' => 'Label atas', 'default' => 'Ucapan & Doa'],
            'heading' => ['label' => 'Judul', 'default' => 'Buku Tamu'],
            'submit' => ['label' => 'Tombol kirim', 'default' => 'Kirim Ucapan'],
        ],
        'gift' => [
            'eyebrow' => ['label' => 'Label atas', 'default' => 'Hadiah Pernikahan'],
            'heading' => ['label' => 'Judul', 'default' => 'Amplop Digital'],
            'note' => ['label' => 'Keterangan', 'default' => 'Doa dan kehadiran Anda adalah hadiah terbaik bagi kami.'],
        ],
        'qr_code' => [
            'eyebrow' => ['label' => 'Label atas', 'default' => 'QR Code Kehadiran'],
            'hint' => ['label' => 'Keterangan', 'default' => 'Tunjukkan QR ini kepada panitia saat tiba di lokasi'],
        ],
        'closing' => [
            'eyebrow' => ['label' => 'Label atas', 'default' => 'Terima Kasih'],
            'body' => ['label' => 'Isi', 'default' => 'Merupakan suatu kehormatan dan kebahagiaan bagi kami apabila Bapak/Ibu/Saudara/i berkenan hadir dan memberikan doa restu.'],
        ],
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
