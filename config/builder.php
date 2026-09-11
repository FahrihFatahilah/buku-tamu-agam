<?php

/**
 * Builder registries.
 *
 * Everything visual the builder can place on an invitation is declared here as
 * data. The builder core never hard-codes a widget, decoration, overlay or
 * animation — it only reads these registries, which is what makes new visuals
 * additive (add an entry + a Blade view, no core changes).
 *
 * Each entry shape:
 *   type            unique key used in the document JSON
 *   name            label shown in the editor
 *   category        palette grouping key
 *   icon            icon token resolved by the editor
 *   description     helper text
 *   view            Blade view rendered by PageRenderer
 *   defaultProps    content defaults
 *   defaultStyles   per-breakpoint style defaults (desktop|tablet|mobile)
 *   allowedChildren bool, or an array of allowed child types
 *   inspector       tabs => field definitions (schema-driven inspector)
 */
$text = fn (string $name, string $label, array $extra = []) => array_merge([
    'type' => 'text', 'name' => $name, 'label' => $label,
], $extra);

$textarea = fn (string $name, string $label) => [
    'type' => 'textarea', 'name' => $name, 'label' => $label,
];

$number = fn (string $name, string $label, array $extra = []) => array_merge([
    'type' => 'number', 'name' => $name, 'label' => $label,
], $extra);

$color = fn (string $name, string $label) => [
    'type' => 'color', 'name' => $name, 'label' => $label,
];

$select = fn (string $name, string $label, array $options) => [
    'type' => 'select', 'name' => $name, 'label' => $label, 'options' => $options,
];

$toggle = fn (string $name, string $label) => [
    'type' => 'switch', 'name' => $name, 'label' => $label,
];

$slider = fn (string $name, string $label, int $min, int $max, int $step = 1) => [
    'type' => 'slider', 'name' => $name, 'label' => $label,
    'min' => $min, 'max' => $max, 'step' => $step,
];

// Returns a LIST of fields — merging a bare assoc field array into a field
// list would collide on the type/name/label keys.
$align = fn () => [$select('textAlign', 'Perataan', [
    ['value' => 'left', 'label' => 'Kiri'],
    ['value' => 'center', 'label' => 'Tengah'],
    ['value' => 'right', 'label' => 'Kanan'],
])];

// ── Shared style control groups ─────────────────────────────────────────────

$styleTypography = fn () => [
    $number('fontSize', 'Ukuran huruf', ['min' => 8, 'max' => 160, 'unit' => 'px']),
    $select('fontWeight', 'Ketebalan', [
        ['value' => '300', 'label' => 'Tipis'],
        ['value' => '400', 'label' => 'Normal'],
        ['value' => '500', 'label' => 'Medium'],
        ['value' => '600', 'label' => 'Semi Bold'],
        ['value' => '700', 'label' => 'Bold'],
    ]),
    $number('letterSpacing', 'Jarak huruf', ['min' => -5, 'max' => 30, 'unit' => 'px']),
    $number('lineHeight', 'Tinggi baris', ['min' => 80, 'max' => 300, 'unit' => 'percent']),
];

$styleColor = fn () => [
    $color('color', 'Warna teks'),
];

$styleBox = fn () => [
    ['type' => 'spacing', 'name' => 'padding', 'label' => 'Padding'],
    ['type' => 'spacing', 'name' => 'margin', 'label' => 'Margin'],
];

$styleBackground = fn () => [
    $color('backgroundColor', 'Warna latar'),
    ['type' => 'image', 'name' => 'backgroundImage', 'label' => 'Gambar latar'],
    ['type' => 'gradient', 'name' => 'backgroundGradient', 'label' => 'Gradien latar'],
];

$styleBorder = fn () => [
    $number('borderWidth', 'Tebal border', ['min' => 0, 'max' => 20, 'unit' => 'px']),
    $color('borderColor', 'Warna border'),
    $number('borderRadius', 'Radius', ['min' => 0, 'max' => 80, 'unit' => 'px']),
    ['type' => 'shadow', 'name' => 'boxShadow', 'label' => 'Bayangan'],
];

$styleBoxLayout = fn () => [
    ['type' => 'spacing', 'name' => 'padding', 'label' => 'Padding'],
    $number('maxWidth', 'Lebar maksimum', ['min' => 240, 'max' => 1600, 'unit' => 'px']),
    $select('justifyContent', 'Perataan horizontal', [
        ['value' => 'flex-start', 'label' => 'Kiri'],
        ['value' => 'center', 'label' => 'Tengah'],
        ['value' => 'flex-end', 'label' => 'Kanan'],
        ['value' => 'space-between', 'label' => 'Sebaran'],
    ]),
    $select('alignItems', 'Perataan vertikal', [
        ['value' => 'flex-start', 'label' => 'Atas'],
        ['value' => 'center', 'label' => 'Tengah'],
        ['value' => 'flex-end', 'label' => 'Bawah'],
    ]),
    $number('gap', 'Jarak antar elemen', ['min' => 0, 'max' => 120, 'unit' => 'px']),
    $slider('opacity', 'Opacity', 0, 100),
];

// ── Style field groups for decorations (rotation/position live in Advanced) ──

return [

    'document_version' => 2,

    // Reject oversized documents before they reach the database.
    'max_document_bytes' => 262144,

    // Max nesting depth for node children.
    'max_depth' => 8,

    'categories' => [
        'basic' => ['label' => 'Dasar', 'order' => 1],
        'layout' => ['label' => 'Tata Letak', 'order' => 2],
        'invitation' => ['label' => 'Undangan', 'order' => 3],
        'decoration' => ['label' => 'Dekorasi', 'order' => 4],
        'overlay' => ['label' => 'Efek', 'order' => 5],
    ],

    /*
    |--------------------------------------------------------------------------
    | Widgets
    |--------------------------------------------------------------------------
    */

    'widgets' => [

        // ── Basic ───────────────────────────────────────────────────────────
        'heading' => [
            'name' => 'Judul',
            'category' => 'basic',
            'icon' => 'heading',
            'description' => 'Teks judul dengan level heading.',
            'view' => 'invitation.widgets.heading',
            'defaultProps' => ['text' => 'Judul Anda', 'level' => 2],
            'defaultStyles' => ['desktop' => ['fontSize' => 36, 'lineHeight' => 120]],
            'allowedChildren' => false,
            'inspector' => [
                'content' => [
                    $text('text', 'Teks'),
                    $select('level', 'Level', [
                        ['value' => 1, 'label' => 'H1'],
                        ['value' => 2, 'label' => 'H2'],
                        ['value' => 3, 'label' => 'H3'],
                    ]),
                ],
                'style' => array_merge($styleTypography(), $styleColor(), $align(), $styleBox()),
                'advanced' => [],
            ],
        ],

        'text' => [
            'name' => 'Teks',
            'category' => 'basic',
            'icon' => 'text',
            'description' => 'Paragraf teks biasa.',
            'view' => 'invitation.widgets.text',
            'defaultProps' => ['text' => 'Tulis sesuatu di sini.'],
            'defaultStyles' => ['desktop' => ['fontSize' => 16, 'lineHeight' => 170]],
            'allowedChildren' => false,
            'inspector' => [
                'content' => [$textarea('text', 'Teks')],
                'style' => array_merge($styleTypography(), $styleColor(), $align(), $styleBox()),
                'advanced' => [],
            ],
        ],

        'rich-text' => [
            'name' => 'Teks Kaya',
            'category' => 'basic',
            'icon' => 'rich-text',
            'description' => 'Teks dengan format tebal, miring, dan tautan.',
            'view' => 'invitation.widgets.rich-text',
            'defaultProps' => ['html' => '<p>Teks dengan <strong>format</strong> dan <em>penekanan</em>.</p>'],
            'defaultStyles' => ['desktop' => ['fontSize' => 16, 'lineHeight' => 170]],
            'allowedChildren' => false,
            'inspector' => [
                'content' => [
                    ['type' => 'rich-text', 'name' => 'html', 'label' => 'Isi'],
                ],
                'style' => array_merge($styleTypography(), $styleColor(), $align(), $styleBox()),
                'advanced' => [],
            ],
        ],

        'image' => [
            'name' => 'Gambar',
            'category' => 'basic',
            'icon' => 'image',
            'description' => 'Gambar dari media undangan.',
            'view' => 'invitation.widgets.image',
            'defaultProps' => ['src' => '', 'alt' => '', 'objectFit' => 'cover'],
            'defaultStyles' => ['desktop' => ['width' => 480, 'borderRadius' => 0]],
            'allowedChildren' => false,
            'inspector' => [
                'content' => [
                    ['type' => 'image', 'name' => 'src', 'label' => 'Gambar'],
                    $text('alt', 'Teks alternatif'),
                    $select('objectFit', 'Penyesuaian', [
                        ['value' => 'cover', 'label' => 'Cover'],
                        ['value' => 'contain', 'label' => 'Contain'],
                        ['value' => 'fill', 'label' => 'Regangkan'],
                    ]),
                ],
                'style' => array_merge(
                    [$number('width', 'Lebar', ['min' => 40, 'max' => 1600, 'unit' => 'px'])],
                    $align(), $styleBorder(), $styleBox()
                ),
                'advanced' => [],
            ],
        ],

        'button' => [
            'name' => 'Tombol',
            'category' => 'basic',
            'icon' => 'button',
            'description' => 'Tombol dengan label dan tautan.',
            'view' => 'invitation.widgets.button',
            'defaultProps' => ['label' => 'Klik di sini', 'href' => '#', 'target' => '_self', 'variant' => 'solid'],
            'defaultStyles' => ['desktop' => ['fontSize' => 14, 'padding' => ['top' => 12, 'right' => 24, 'bottom' => 12, 'left' => 24], 'borderRadius' => 2]],
            'allowedChildren' => false,
            'inspector' => [
                'content' => [
                    $text('label', 'Label'),
                    ['type' => 'link', 'name' => 'href', 'label' => 'Tautan'],
                    $select('target', 'Buka di', [
                        ['value' => '_self', 'label' => 'Tab yang sama'],
                        ['value' => '_blank', 'label' => 'Tab baru'],
                    ]),
                    $select('variant', 'Gaya', [
                        ['value' => 'solid', 'label' => 'Solid'],
                        ['value' => 'outline', 'label' => 'Outline'],
                        ['value' => 'ghost', 'label' => 'Ghost'],
                    ]),
                ],
                'style' => array_merge(
                    [$number('fontSize', 'Ukuran huruf', ['min' => 8, 'max' => 40, 'unit' => 'px'])],
                    [$color('color', 'Warna teks'), $color('backgroundColor', 'Warna latar')],
                    $align(), $styleBorder(), $styleBox()
                ),
                'advanced' => [],
            ],
        ],

        'icon' => [
            'name' => 'Ikon',
            'category' => 'basic',
            'icon' => 'star',
            'description' => 'Ikon sederhana.',
            'view' => 'invitation.widgets.icon',
            'defaultProps' => ['name' => 'heart', 'size' => 32],
            'defaultStyles' => ['desktop' => []],
            'allowedChildren' => false,
            'inspector' => [
                'content' => [
                    $select('name', 'Ikon', [
                        ['value' => 'heart', 'label' => 'Hati'],
                        ['value' => 'ring', 'label' => 'Cincin'],
                        ['value' => 'flower', 'label' => 'Bunga'],
                        ['value' => 'star', 'label' => 'Bintang'],
                        ['value' => 'location', 'label' => 'Lokasi'],
                        ['value' => 'calendar', 'label' => 'Kalender'],
                        ['value' => 'music', 'label' => 'Musik'],
                        ['value' => 'gift', 'label' => 'Hadiah'],
                    ]),
                    $number('size', 'Ukuran', ['min' => 8, 'max' => 200, 'unit' => 'px']),
                ],
                'style' => array_merge($styleColor(), $styleBox()),
                'advanced' => [],
            ],
        ],

        'divider' => [
            'name' => 'Pembatas',
            'category' => 'basic',
            'icon' => 'minus',
            'description' => 'Garis pemisah.',
            'view' => 'invitation.widgets.divider',
            'tag' => 'div',
            'defaultProps' => ['style' => 'solid'],
            'defaultStyles' => ['desktop' => ['width' => 120, 'borderWidth' => 1]],
            'allowedChildren' => false,
            'inspector' => [
                'content' => [
                    $select('style', 'Gaya', [
                        ['value' => 'solid', 'label' => 'Garis'],
                        ['value' => 'dashed', 'label' => 'Putus-putus'],
                        ['value' => 'dotted', 'label' => 'Titik'],
                    ]),
                ],
                'style' => array_merge(
                    [$number('width', 'Panjang', ['min' => 10, 'max' => 1600, 'unit' => 'px'])],
                    [$number('borderWidth', 'Tebal', ['min' => 1, 'max' => 20, 'unit' => 'px']), $color('borderColor', 'Warna')],
                    $styleBox()
                ),
                'advanced' => [],
            ],
        ],

        'spacer' => [
            'name' => 'Spasi',
            'category' => 'basic',
            'icon' => 'spacer',
            'description' => 'Ruang kosong.',
            'view' => 'invitation.widgets.spacer',
            'defaultProps' => [],
            'defaultStyles' => ['desktop' => ['height' => 40]],
            'allowedChildren' => false,
            'inspector' => [
                'content' => [],
                'style' => [
                    $number('height', 'Tinggi', ['min' => 4, 'max' => 600, 'unit' => 'px']),
                ],
                'advanced' => [],
            ],
        ],

        // ── Layout ──────────────────────────────────────────────────────────
        'section' => [
            'name' => 'Section',
            'category' => 'layout',
            'icon' => 'section',
            'description' => 'Blok besar berisi elemen lain.',
            'view' => 'invitation.widgets.section',
            'tag' => 'section',
            'defaultProps' => [],
            'defaultStyles' => ['desktop' => ['padding' => ['top' => 80, 'right' => 24, 'bottom' => 80, 'left' => 24]]],
            'allowedChildren' => true,
            'inspector' => [
                'content' => [],
                'style' => array_merge($styleBoxLayout(), $styleBackground()),
                'advanced' => [],
            ],
        ],

        'container' => [
            'name' => 'Container',
            'category' => 'layout',
            'icon' => 'container',
            'description' => 'Wadah dengan lebar maksimum.',
            'view' => 'invitation.widgets.container',
            'defaultProps' => [],
            'defaultStyles' => ['desktop' => ['maxWidth' => 720, 'padding' => ['top' => 0, 'right' => 0, 'bottom' => 0, 'left' => 0]]],
            'allowedChildren' => true,
            'inspector' => [
                'content' => [],
                'style' => $styleBoxLayout(),
                'advanced' => [],
            ],
        ],

        'column' => [
            'name' => 'Kolom',
            'category' => 'layout',
            'icon' => 'column',
            'description' => 'Kolom di dalam grid.',
            'view' => 'invitation.widgets.column',
            'defaultProps' => [],
            'defaultStyles' => ['desktop' => []],
            'allowedChildren' => true,
            'inspector' => [
                'content' => [],
                'style' => $styleBoxLayout(),
                'advanced' => [],
            ],
        ],

        'grid' => [
            'name' => 'Grid',
            'category' => 'layout',
            'icon' => 'grid',
            'description' => 'Susunan kolom berjajar.',
            'view' => 'invitation.widgets.grid',
            'defaultProps' => ['columns' => 2],
            'defaultStyles' => ['desktop' => ['gap' => 16], 'tablet' => ['gap' => 12], 'mobile' => ['gap' => 10]],
            'allowedChildren' => ['column'],
            'inspector' => [
                'content' => [
                    $slider('columns', 'Jumlah kolom', 1, 4),
                ],
                'style' => array_merge(
                    [$number('gap', 'Jarak antar kolom', ['min' => 0, 'max' => 80, 'unit' => 'px'])],
                    $styleBoxLayout()
                ),
                'advanced' => [],
            ],
        ],

        // ── Invitation ──────────────────────────────────────────────────────
        'hero' => [
            'name' => 'Hero',
            'category' => 'invitation',
            'icon' => 'hero',
            'description' => 'Pembuka utama dengan nama mempelai dan tanggal.',
            'view' => 'invitation.widgets.hero',
            'defaultProps' => ['eyebrow' => 'The Wedding Of', 'useNickname' => true, 'showDate' => true, 'showVenue' => true, 'heroImage' => true],
            'defaultStyles' => ['desktop' => ['minHeight' => 560, 'textAlign' => 'center']],
            'allowedChildren' => false,
            'inspector' => [
                'content' => [
                    $text('eyebrow', 'Label atas'),
                    $toggle('useNickname', 'Gunakan nama panggilan'),
                    $toggle('showDate', 'Tampilkan tanggal'),
                    $toggle('showVenue', 'Tampilkan lokasi'),
                    $toggle('heroImage', 'Gunakan foto hero jika ada'),
                ],
                'style' => array_merge($styleTypography(), $styleColor(), $align(), $styleBox(), $styleBackground()),
                'advanced' => [],
            ],
        ],

        'video' => [
            'name' => 'Video',
            'category' => 'invitation',
            'icon' => 'video',
            'description' => 'Video dari media undangan.',
            'view' => 'invitation.widgets.video',
            'defaultProps' => ['eyebrow' => 'Video', 'heading' => 'Momen Bergerak'],
            'defaultStyles' => ['desktop' => ['maxWidth' => 720]],
            'allowedChildren' => false,
            'inspector' => [
                'content' => [
                    $text('eyebrow', 'Label atas'),
                    $text('heading', 'Judul'),
                ],
                'style' => array_merge($styleTypography(), $styleColor(), $align(), $styleBox(), $styleBackground()),
                'advanced' => [],
            ],
        ],

        'couple-names' => [
            'name' => 'Nama Mempelai',
            'category' => 'invitation',
            'icon' => 'heart',
            'description' => 'Nama kedua mempelai (dari data undangan).',
            'view' => 'invitation.widgets.couple-names',
            'defaultProps' => ['source' => 'invitation.couple_names', 'showNickname' => true, 'showParents' => false],
            'defaultStyles' => ['desktop' => ['fontSize' => 32, 'textAlign' => 'center']],
            'allowedChildren' => false,
            'inspector' => [
                'content' => [
                    $toggle('showNickname', 'Tampilkan nama panggilan'),
                    $toggle('showParents', 'Tampilkan nama orang tua'),
                    $text('eyebrow', 'Label atas'),
                ],
                'style' => array_merge($styleTypography(), $styleColor(), $align(), $styleBox()),
                'advanced' => [],
            ],
        ],

        'wedding-date' => [
            'name' => 'Tanggal Pernikahan',
            'category' => 'invitation',
            'icon' => 'calendar',
            'description' => 'Tanggal acara (dari data undangan).',
            'view' => 'invitation.widgets.wedding-date',
            'defaultProps' => ['source' => 'invitation.date', 'format' => 'long'],
            'defaultStyles' => ['desktop' => ['fontSize' => 18, 'textAlign' => 'center']],
            'allowedChildren' => false,
            'inspector' => [
                'content' => [
                    $select('format', 'Format', [
                        ['value' => 'long', 'label' => 'Lengkap (Sabtu, 15 Juni 2025)'],
                        ['value' => 'medium', 'label' => 'Sedang (15 Juni 2025)'],
                        ['value' => 'short', 'label' => 'Singkat (15/06/2025)'],
                    ]),
                ],
                'style' => array_merge($styleTypography(), $styleColor(), $align(), $styleBox()),
                'advanced' => [],
            ],
        ],

        'countdown' => [
            'name' => 'Countdown',
            'category' => 'invitation',
            'icon' => 'clock',
            'description' => 'Hitung mundur ke hari acara.',
            'view' => 'invitation.widgets.countdown',
            'defaultProps' => ['label' => 'Menuju Hari Bahagia', 'showSeconds' => true],
            'defaultStyles' => ['desktop' => ['textAlign' => 'center']],
            'allowedChildren' => false,
            'inspector' => [
                'content' => [
                    $text('label', 'Label'),
                    $toggle('showSeconds', 'Tampilkan detik'),
                ],
                'style' => array_merge($styleTypography(), $styleColor(), $align(), $styleBox()),
                'advanced' => [],
            ],
        ],

        'event-details' => [
            'name' => 'Detail Acara',
            'category' => 'invitation',
            'icon' => 'list',
            'description' => 'Daftar rangkaian acara (akad, resepsi).',
            'view' => 'invitation.widgets.event-details',
            'defaultProps' => ['eyebrow' => 'Rangkaian Acara', 'heading' => 'Jadwal Acara', 'showDressCode' => true],
            'defaultStyles' => ['desktop' => []],
            'allowedChildren' => false,
            'inspector' => [
                'content' => [
                    $text('eyebrow', 'Label atas'),
                    $text('heading', 'Judul'),
                    $toggle('showDressCode', 'Tampilkan dress code'),
                ],
                'style' => array_merge($styleTypography(), $styleColor(), $align(), $styleBox(), $styleBackground()),
                'advanced' => [],
            ],
        ],

        'location' => [
            'name' => 'Lokasi',
            'category' => 'invitation',
            'icon' => 'location',
            'description' => 'Nama tempat dan alamat acara.',
            'view' => 'invitation.widgets.location',
            'defaultProps' => ['eyebrow' => 'Lokasi', 'heading' => 'Tempat Acara', 'showButton' => true, 'buttonLabel' => 'Buka Google Maps'],
            'defaultStyles' => ['desktop' => ['textAlign' => 'center']],
            'allowedChildren' => false,
            'inspector' => [
                'content' => [
                    $text('eyebrow', 'Label atas'),
                    $text('heading', 'Judul'),
                    $toggle('showButton', 'Tampilkan tombol peta'),
                    $text('buttonLabel', 'Label tombol'),
                ],
                'style' => array_merge($styleTypography(), $styleColor(), $align(), $styleBox(), $styleBackground()),
                'advanced' => [],
            ],
        ],

        'maps' => [
            'name' => 'Peta',
            'category' => 'invitation',
            'icon' => 'map',
            'description' => 'Peta lokasi atau tautan ke Google Maps.',
            'view' => 'invitation.widgets.maps',
            'defaultProps' => ['mode' => 'button', 'buttonLabel' => 'Lihat Lokasi di Google Maps'],
            'defaultStyles' => ['desktop' => ['textAlign' => 'center']],
            'allowedChildren' => false,
            'inspector' => [
                'content' => [
                    $select('mode', 'Mode', [
                        ['value' => 'button', 'label' => 'Tombol tautan'],
                        ['value' => 'embed', 'label' => 'Peta tertanam'],
                    ]),
                    $text('buttonLabel', 'Label tombol'),
                    $number('height', 'Tinggi peta', ['min' => 160, 'max' => 800, 'unit' => 'px']),
                ],
                'style' => array_merge($styleColor(), $align(), $styleBox()),
                'advanced' => [],
            ],
        ],

        'rsvp' => [
            'name' => 'RSVP',
            'category' => 'invitation',
            'icon' => 'check',
            'description' => 'Form konfirmasi kehadiran (per tamu).',
            'view' => 'invitation.widgets.rsvp',
            'defaultProps' => ['eyebrow' => 'Konfirmasi Kehadiran', 'heading' => 'RSVP', 'submit' => 'Kirim Konfirmasi'],
            'defaultStyles' => ['desktop' => ['textAlign' => 'center']],
            'allowedChildren' => false,
            'requiresGuest' => true,
            'inspector' => [
                'content' => [
                    $text('eyebrow', 'Label atas'),
                    $text('heading', 'Judul'),
                    $text('submit', 'Label tombol kirim'),
                ],
                'style' => array_merge($styleTypography(), $styleColor(), $align(), $styleBox(), $styleBackground()),
                'advanced' => [],
            ],
        ],

        'gallery' => [
            'name' => 'Galeri',
            'category' => 'invitation',
            'icon' => 'gallery',
            'description' => 'Galeri foto dari media undangan.',
            'view' => 'invitation.widgets.gallery',
            'defaultProps' => ['eyebrow' => 'Galeri', 'heading' => 'Momen Bersama', 'collections' => ['gallery', 'prewedding'], 'columns' => 3],
            'defaultStyles' => ['desktop' => ['gap' => 8]],
            'allowedChildren' => false,
            'inspector' => [
                'content' => [
                    $text('eyebrow', 'Label atas'),
                    $text('heading', 'Judul'),
                    $slider('columns', 'Jumlah kolom', 1, 4),
                ],
                'style' => array_merge($styleTypography(), $styleColor(), $align(), $styleBox(), $styleBackground()),
                'advanced' => [],
            ],
        ],

        'love-story' => [
            'name' => 'Kisah Cinta',
            'category' => 'invitation',
            'icon' => 'book',
            'description' => 'Kisah perjalanan kedua mempelai.',
            'view' => 'invitation.widgets.love-story',
            'defaultProps' => ['eyebrow' => 'Perjalanan Kami', 'heading' => 'Kisah Cinta', 'stories' => [
                ['year' => '2019', 'title' => 'Pertama Bertemu', 'description' => 'Kami dipertemukan di sebuah acara kampus.'],
            ]],
            'defaultStyles' => ['desktop' => []],
            'allowedChildren' => false,
            'inspector' => [
                'content' => [
                    $text('eyebrow', 'Label atas'),
                    $text('heading', 'Judul'),
                    ['type' => 'repeater', 'name' => 'stories', 'label' => 'Daftar kisah', 'fields' => [
                        $text('year', 'Tahun'),
                        $text('title', 'Judul'),
                        $textarea('description', 'Deskripsi'),
                    ]],
                ],
                'style' => array_merge($styleTypography(), $styleColor(), $align(), $styleBox(), $styleBackground()),
                'advanced' => [],
            ],
        ],

        'timeline' => [
            'name' => 'Timeline',
            'category' => 'invitation',
            'icon' => 'timeline',
            'description' => 'Susunan acara per jam.',
            'view' => 'invitation.widgets.timeline',
            'defaultProps' => ['eyebrow' => 'Susunan Acara', 'heading' => 'Timeline', 'items' => [
                ['time' => '08.00', 'title' => 'Akad Nikah', 'description' => ''],
            ]],
            'defaultStyles' => ['desktop' => []],
            'allowedChildren' => false,
            'inspector' => [
                'content' => [
                    $text('eyebrow', 'Label atas'),
                    $text('heading', 'Judul'),
                    ['type' => 'repeater', 'name' => 'items', 'label' => 'Daftar acara', 'fields' => [
                        $text('time', 'Waktu'),
                        $text('title', 'Judul'),
                        $textarea('description', 'Deskripsi'),
                    ]],
                ],
                'style' => array_merge($styleTypography(), $styleColor(), $align(), $styleBox(), $styleBackground()),
                'advanced' => [],
            ],
        ],

        'gift' => [
            'name' => 'Hadiah',
            'category' => 'invitation',
            'icon' => 'gift',
            'description' => 'Metode hadiah (transfer bank, QRIS).',
            'view' => 'invitation.widgets.gift',
            'defaultProps' => ['eyebrow' => 'Hadiah Pernikahan', 'heading' => 'Amplop Digital', 'note' => 'Doa dan kehadiran Anda adalah hadiah terbaik bagi kami.'],
            'defaultStyles' => ['desktop' => ['textAlign' => 'center']],
            'allowedChildren' => false,
            'inspector' => [
                'content' => [
                    $text('eyebrow', 'Label atas'),
                    $text('heading', 'Judul'),
                    $textarea('note', 'Keterangan'),
                ],
                'style' => array_merge($styleTypography(), $styleColor(), $align(), $styleBox(), $styleBackground()),
                'advanced' => [],
            ],
        ],

        'bank-account' => [
            'name' => 'Rekening Bank',
            'category' => 'invitation',
            'icon' => 'card',
            'description' => 'Satu rekening bank tertentu.',
            'view' => 'invitation.widgets.bank-account',
            'defaultProps' => ['giftId' => null, 'showCopy' => true],
            'defaultStyles' => ['desktop' => []],
            'allowedChildren' => false,
            'inspector' => [
                'content' => [
                    ['type' => 'gift-select', 'name' => 'giftId', 'label' => 'Metode hadiah'],
                    $toggle('showCopy', 'Tampilkan tombol salin'),
                ],
                'style' => array_merge($styleTypography(), $styleColor(), $align(), $styleBox()),
                'advanced' => [],
            ],
        ],

        'guestbook' => [
            'name' => 'Buku Tamu',
            'category' => 'invitation',
            'icon' => 'message',
            'description' => 'Ucapan dan doa dari tamu.',
            'view' => 'invitation.widgets.guestbook',
            'defaultProps' => ['eyebrow' => 'Ucapan & Doa', 'heading' => 'Buku Tamu', 'submit' => 'Kirim Ucapan', 'limit' => 20],
            'defaultStyles' => ['desktop' => ['textAlign' => 'center']],
            'allowedChildren' => false,
            'inspector' => [
                'content' => [
                    $text('eyebrow', 'Label atas'),
                    $text('heading', 'Judul'),
                    $text('submit', 'Label tombol kirim'),
                    $slider('limit', 'Jumlah ucapan tampil', 5, 50),
                ],
                'style' => array_merge($styleTypography(), $styleColor(), $align(), $styleBox(), $styleBackground()),
                'advanced' => [],
            ],
        ],

        'quote' => [
            'name' => 'Kutipan',
            'category' => 'invitation',
            'icon' => 'quote',
            'description' => 'Kutipan atau ayat.',
            'view' => 'invitation.widgets.quote',
            'defaultProps' => ['source' => 'invitation.quote', 'showSource' => true],
            'defaultStyles' => ['desktop' => ['fontSize' => 20, 'textAlign' => 'center']],
            'allowedChildren' => false,
            'inspector' => [
                'content' => [
                    $toggle('showSource', 'Tampilkan sumber'),
                ],
                'style' => array_merge($styleTypography(), $styleColor(), $align(), $styleBox(), $styleBackground()),
                'advanced' => [],
            ],
        ],

        // ── Special ─────────────────────────────────────────────────────────
        'opening' => [
            'name' => 'Pembuka',
            'category' => 'invitation',
            'icon' => 'door',
            'description' => 'Layar pembuka sebelum undangan dibuka.',
            'view' => 'invitation.widgets.opening',
            'defaultProps' => ['eyebrow' => 'Undangan Pernikahan', 'cta' => 'Buka Undangan', 'showGuestName' => true],
            'defaultStyles' => ['desktop' => []],
            'allowedChildren' => false,
            'inspector' => [
                'content' => [
                    $text('eyebrow', 'Label atas'),
                    $text('cta', 'Label tombol'),
                    $toggle('showGuestName', 'Tampilkan nama tamu'),
                ],
                'style' => array_merge($styleColor(), $styleBox()),
                'advanced' => [],
            ],
        ],

        'closing' => [
            'name' => 'Penutup',
            'category' => 'invitation',
            'icon' => 'flag',
            'description' => 'Ucapan terima kasih di akhir undangan.',
            'view' => 'invitation.widgets.closing',
            'defaultProps' => ['eyebrow' => 'Terima Kasih', 'body' => 'Merupakan suatu kehormatan dan kebahagiaan bagi kami apabila Bapak/Ibu/Saudara/i berkenan hadir dan memberikan doa restu.', 'showCouple' => true],
            'defaultStyles' => ['desktop' => ['textAlign' => 'center']],
            'allowedChildren' => false,
            'inspector' => [
                'content' => [
                    $text('eyebrow', 'Label atas'),
                    $textarea('body', 'Isi'),
                    $toggle('showCouple', 'Tampilkan nama mempelai'),
                ],
                'style' => array_merge($styleTypography(), $styleColor(), $align(), $styleBox(), $styleBackground()),
                'advanced' => [],
            ],
        ],

        'guest-name' => [
            'name' => 'Nama Tamu',
            'category' => 'invitation',
            'icon' => 'user',
            'description' => 'Nama tamu (khusus undangan personal).',
            'view' => 'invitation.widgets.guest-name',
            'defaultProps' => ['source' => 'guest.name', 'prefix' => 'Kepada Yth.'],
            'defaultStyles' => ['desktop' => ['fontSize' => 18, 'textAlign' => 'center']],
            'allowedChildren' => false,
            'requiresGuest' => true,
            'inspector' => [
                'content' => [
                    $text('prefix', 'Awalan'),
                ],
                'style' => array_merge($styleTypography(), $styleColor(), $align(), $styleBox()),
                'advanced' => [],
            ],
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Decorations — free-form absolutely positioned objects
    |--------------------------------------------------------------------------
    */

    'decorations' => [
        'flower' => ['name' => 'Bunga', 'asset' => 'flower', 'defaultStyles' => ['desktop' => ['x' => 24, 'y' => 40, 'width' => 140, 'rotation' => -12, 'opacity' => 90, 'zIndex' => 20]]],
        'leaf' => ['name' => 'Daun', 'asset' => 'leaf', 'defaultStyles' => ['desktop' => ['x' => 20, 'y' => 80, 'width' => 120, 'rotation' => 18, 'opacity' => 85, 'zIndex' => 20]]],
        'sparkle' => ['name' => 'Kerlip', 'asset' => 'sparkle', 'defaultStyles' => ['desktop' => ['x' => 60, 'y' => 60, 'width' => 60, 'opacity' => 80, 'zIndex' => 25]]],
        'heart' => ['name' => 'Hati', 'asset' => 'heart', 'defaultStyles' => ['desktop' => ['x' => 40, 'y' => 60, 'width' => 70, 'rotation' => -8, 'opacity' => 85, 'zIndex' => 20]]],
        'butterfly' => ['name' => 'Kupu-kupu', 'asset' => 'butterfly', 'defaultStyles' => ['desktop' => ['x' => 80, 'y' => 120, 'width' => 90, 'rotation' => 10, 'opacity' => 90, 'zIndex' => 22]]],
        'star' => ['name' => 'Bintang', 'asset' => 'star', 'defaultStyles' => ['desktop' => ['x' => 30, 'y' => 30, 'width' => 50, 'opacity' => 80, 'zIndex' => 20]]],
        'ribbon' => ['name' => 'Pita', 'asset' => 'ribbon', 'defaultStyles' => ['desktop' => ['x' => 0, 'y' => 0, 'width' => 200, 'opacity' => 90, 'zIndex' => 15]]],
        'frame' => ['name' => 'Bingkai', 'asset' => 'frame', 'defaultStyles' => ['desktop' => ['x' => 16, 'y' => 16, 'width' => 320, 'opacity' => 70, 'zIndex' => 10]]],
    ],

    /*
    |--------------------------------------------------------------------------
    | Overlays — global layers above/below the invitation content
    |--------------------------------------------------------------------------
    */

    'overlays' => [
        'falling-petals' => [
            'name' => 'Kelopak Berguguran',
            'animated' => true,
            'view' => 'invitation.overlays.falling-petals',
            'defaultProps' => ['density' => 18, 'speed' => 'normal', 'size' => 18, 'color' => '#E8B4B8'],
            'defaultStyles' => ['desktop' => ['opacity' => 70, 'zIndex' => 30]],
            'inspector' => [
                $slider('density', 'Kepadatan', 4, 60),
                $select('speed', 'Kecepatan', [
                    ['value' => 'slow', 'label' => 'Lambat'],
                    ['value' => 'normal', 'label' => 'Normal'],
                    ['value' => 'fast', 'label' => 'Cepat'],
                ]),
                $number('size', 'Ukuran', ['min' => 6, 'max' => 80, 'unit' => 'px']),
                $color('color', 'Warna'),
            ],
        ],
        'floating-hearts' => [
            'name' => 'Hati Melayang',
            'animated' => true,
            'view' => 'invitation.overlays.floating-hearts',
            'defaultProps' => ['density' => 14, 'speed' => 'normal', 'size' => 20, 'color' => '#E8A0A8'],
            'defaultStyles' => ['desktop' => ['opacity' => 65, 'zIndex' => 30]],
            'inspector' => [
                $slider('density', 'Kepadatan', 4, 60),
                $select('speed', 'Kecepatan', [
                    ['value' => 'slow', 'label' => 'Lambat'],
                    ['value' => 'normal', 'label' => 'Normal'],
                    ['value' => 'fast', 'label' => 'Cepat'],
                ]),
                $number('size', 'Ukuran', ['min' => 6, 'max' => 80, 'unit' => 'px']),
                $color('color', 'Warna'),
            ],
        ],
        'sparkles' => [
            'name' => 'Kerlipan',
            'animated' => true,
            'view' => 'invitation.overlays.sparkles',
            'defaultProps' => ['density' => 22, 'speed' => 'normal', 'size' => 14, 'color' => '#F5D98B'],
            'defaultStyles' => ['desktop' => ['opacity' => 75, 'zIndex' => 30]],
            'inspector' => [
                $slider('density', 'Kepadatan', 4, 60),
                $select('speed', 'Kecepatan', [
                    ['value' => 'slow', 'label' => 'Lambat'],
                    ['value' => 'normal', 'label' => 'Normal'],
                    ['value' => 'fast', 'label' => 'Cepat'],
                ]),
                $number('size', 'Ukuran', ['min' => 4, 'max' => 60, 'unit' => 'px']),
                $color('color', 'Warna'),
            ],
        ],
        'fireflies' => [
            'name' => 'Kunang-kunang',
            'animated' => true,
            'view' => 'invitation.overlays.fireflies',
            'defaultProps' => ['density' => 16, 'speed' => 'slow', 'size' => 10, 'color' => '#FFE9A8'],
            'defaultStyles' => ['desktop' => ['opacity' => 80, 'zIndex' => 30]],
            'inspector' => [
                $slider('density', 'Kepadatan', 4, 60),
                $select('speed', 'Kecepatan', [
                    ['value' => 'slow', 'label' => 'Lambat'],
                    ['value' => 'normal', 'label' => 'Normal'],
                    ['value' => 'fast', 'label' => 'Cepat'],
                ]),
                $number('size', 'Ukuran', ['min' => 4, 'max' => 40, 'unit' => 'px']),
                $color('color', 'Warna'),
            ],
        ],
        'confetti' => [
            'name' => 'Confetti',
            'animated' => true,
            'view' => 'invitation.overlays.confetti',
            'defaultProps' => ['density' => 24, 'speed' => 'fast', 'size' => 12, 'color' => '#C9A84C'],
            'defaultStyles' => ['desktop' => ['opacity' => 80, 'zIndex' => 30]],
            'inspector' => [
                $slider('density', 'Kepadatan', 4, 60),
                $select('speed', 'Kecepatan', [
                    ['value' => 'slow', 'label' => 'Lambat'],
                    ['value' => 'normal', 'label' => 'Normal'],
                    ['value' => 'fast', 'label' => 'Cepat'],
                ]),
                $number('size', 'Ukuran', ['min' => 4, 'max' => 40, 'unit' => 'px']),
                $color('color', 'Warna'),
            ],
        ],
        'gradient' => [
            'name' => 'Gradien',
            'animated' => false,
            'view' => 'invitation.overlays.gradient',
            'defaultProps' => ['from' => '#000000', 'to' => '#00000000', 'direction' => 'to-top'],
            'defaultStyles' => ['desktop' => ['opacity' => 50, 'zIndex' => 20]],
            'inspector' => [
                $color('from', 'Dari warna'),
                $color('to', 'Ke warna'),
                $select('direction', 'Arah', [
                    ['value' => 'to-top', 'label' => 'Ke atas'],
                    ['value' => 'to-bottom', 'label' => 'Ke bawah'],
                    ['value' => 'to-left', 'label' => 'Ke kiri'],
                    ['value' => 'to-right', 'label' => 'Ke kanan'],
                ]),
            ],
        ],
        'pattern' => [
            'name' => 'Pola',
            'animated' => false,
            'view' => 'invitation.overlays.pattern',
            'defaultProps' => ['motif' => 'dots', 'size' => 16, 'color' => '#00000010'],
            'defaultStyles' => ['desktop' => ['opacity' => 40, 'zIndex' => 20]],
            'inspector' => [
                $select('motif', 'Motif', [
                    ['value' => 'dots', 'label' => 'Titik'],
                    ['value' => 'grid', 'label' => 'Garis'],
                    ['value' => 'diagonal', 'label' => 'Diagonal'],
                ]),
                $number('size', 'Ukuran', ['min' => 4, 'max' => 80, 'unit' => 'px']),
                $color('color', 'Warna'),
            ],
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Animations — reusable across widgets, sections and decorations
    |--------------------------------------------------------------------------
    */

    'animations' => [
        // Entrance
        'fade' => ['name' => 'Fade', 'category' => 'entrance'],
        'fade-up' => ['name' => 'Fade Up', 'category' => 'entrance'],
        'fade-down' => ['name' => 'Fade Down', 'category' => 'entrance'],
        'fade-left' => ['name' => 'Fade Left', 'category' => 'entrance'],
        'fade-right' => ['name' => 'Fade Right', 'category' => 'entrance'],
        'zoom' => ['name' => 'Zoom', 'category' => 'entrance'],
        'slide' => ['name' => 'Slide', 'category' => 'entrance'],
        'bounce' => ['name' => 'Bounce', 'category' => 'entrance'],
        'rotate' => ['name' => 'Rotate', 'category' => 'entrance'],
        'flip' => ['name' => 'Flip', 'category' => 'entrance'],

        // Continuous
        'float' => ['name' => 'Float', 'category' => 'continuous'],
        'sway' => ['name' => 'Sway', 'category' => 'continuous'],
        'swing' => ['name' => 'Swing', 'category' => 'continuous'],
        'pulse' => ['name' => 'Pulse', 'category' => 'continuous'],
        'shake' => ['name' => 'Shake', 'category' => 'continuous'],
        'shimmer' => ['name' => 'Shimmer', 'category' => 'continuous'],
        'spin' => ['name' => 'Spin', 'category' => 'continuous'],
        'breathing' => ['name' => 'Breathing', 'category' => 'continuous'],

        // Scroll
        'parallax' => ['name' => 'Parallax', 'category' => 'scroll'],
        'fade-scroll' => ['name' => 'Fade on Scroll', 'category' => 'scroll'],
        'move-scroll' => ['name' => 'Move on Scroll', 'category' => 'scroll'],
        'scale-scroll' => ['name' => 'Scale on Scroll', 'category' => 'scroll'],
        'rotate-scroll' => ['name' => 'Rotate on Scroll', 'category' => 'scroll'],
    ],

    'animation_categories' => [
        'entrance' => ['label' => 'Muncul', 'order' => 1],
        'continuous' => ['label' => 'Terus-menerus', 'order' => 2],
        'scroll' => ['label' => 'Saat Scroll', 'order' => 3],
    ],

    /*
    |--------------------------------------------------------------------------
    | Effects — node-attached behaviours (not global layers)
    |--------------------------------------------------------------------------
    */

    'effects' => [
        'mouse-parallax' => [
            'name' => 'Mouse Parallax',
            'description' => 'Elemen bergeser mengikuti gerakan kursor.',
            'inspector' => [
                $slider('strength', 'Kekuatan', 0, 100),
            ],
        ],
        'cursor-glow' => [
            'name' => 'Cursor Glow',
            'description' => 'Cahaya lembut mengikuti kursor.',
            'inspector' => [
                $number('size', 'Ukuran', ['min' => 40, 'max' => 600, 'unit' => 'px']),
                $color('color', 'Warna'),
            ],
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Animation presets — applied as global intensity/duration multipliers
    |--------------------------------------------------------------------------
    |
    | These finally make `weddings.animation_config` take effect.
    |
    */

    'animation_presets' => [
        'elegant' => ['label' => 'Elegant', 'duration' => 1.0, 'intensity' => 1.0],
        'cinematic' => ['label' => 'Cinematic', 'duration' => 1.4, 'intensity' => 1.2],
        'romantic' => ['label' => 'Romantic', 'duration' => 1.2, 'intensity' => 0.9],
        'traditional' => ['label' => 'Traditional', 'duration' => 1.0, 'intensity' => 1.0],
        'minimal' => ['label' => 'Minimal', 'duration' => 0.7, 'intensity' => 0.6],
        'none' => ['label' => 'Tanpa Animasi', 'duration' => 0, 'intensity' => 0],
    ],
];
