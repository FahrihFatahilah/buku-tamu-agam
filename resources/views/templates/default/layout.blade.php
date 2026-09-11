{{--
    Default template — the fallback used by TemplateService when a wedding's
    template key has no view directory, or a template is missing a section view.

    Colours and fonts come from the template's default_settings, so templates
    created in the builder (which have no Blade files) render distinctly.
--}}
@php
    $tplSettings = $wedding->template->default_settings ?? [];
    $palette     = $tplSettings['palette'] ?? [];
    $fontCfg     = $tplSettings['fonts'] ?? [];

    $tplPrimary   = $palette['primary']   ?? '#1A1A1A';
    $tplSecondary = $palette['secondary'] ?? '#FFFFFF';
    $tplAccent    = $palette['accent']    ?? '#888888';
    $tplDark      = $palette['dark']      ?? '#1A1A1A';

    $tplFontDisplay = $fontCfg['display'] ?? 'EB Garamond';
    $tplFontBody    = $fontCfg['body']    ?? 'Inter';
    $gfont = fn($f) => str_replace(' ', '+', trim($f));
@endphp
<!DOCTYPE html>
<html lang="id" class="scroll-smooth">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $wedding->bride_name }} & {{ $wedding->groom_name }} — Undangan Pernikahan</title>
    <meta name="description" content="{{ $wedding->seo_description ?? $wedding->description }}">
    <meta property="og:title" content="{{ $wedding->seo_title ?? $wedding->coupleName() }}">
    <meta property="og:description" content="{{ $wedding->seo_description ?? $wedding->description }}">
    @if($wedding->og_image)
    <meta property="og:image" content="{{ Storage::url($wedding->og_image) }}">
    @endif
    <meta property="og:type" content="website">
    <link rel="canonical" href="{{ $wedding->publicUrl() }}">
    @if($wedding->favicon)<link rel="icon" href="{{ Storage::url($wedding->favicon) }}">@endif
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family={{ $gfont($tplFontDisplay) }}&family={{ $gfont($tplFontBody) }}&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        :root {
            --tpl-primary:   {{ $tplPrimary }};
            --tpl-secondary: {{ $tplSecondary }};
            --tpl-accent:    {{ $tplAccent }};
            --tpl-dark:      {{ $tplDark }};
        }

        body {
            font-family: '{{ $tplFontBody }}', sans-serif;
            background: var(--tpl-secondary);
            color: var(--tpl-dark);
        }

        .tpl-display  { font-family: '{{ $tplFontDisplay }}', serif; }
        .tpl-ink      { color: var(--tpl-dark); }
        .tpl-primary  { color: var(--tpl-primary); }
        .tpl-accent   { color: var(--tpl-accent); }
        .tpl-surface  { background: var(--tpl-secondary); }
        .tpl-panel    { background: color-mix(in srgb, var(--tpl-secondary) 92%, var(--tpl-dark)); }
        .tpl-muted    { color: color-mix(in srgb, var(--tpl-dark) 55%, transparent); }
        .tpl-faint    { color: color-mix(in srgb, var(--tpl-dark) 35%, transparent); }
        .tpl-hairline { border-color: color-mix(in srgb, var(--tpl-dark) 14%, transparent); }
        .tpl-rule     { background: color-mix(in srgb, var(--tpl-dark) 18%, transparent); }
        .tpl-btn {
            border-color: color-mix(in srgb, var(--tpl-accent) 55%, transparent);
            color: var(--tpl-accent);
        }
        .tpl-btn:hover { background: color-mix(in srgb, var(--tpl-accent) 10%, transparent); }
        .tpl-btn-solid {
            background: var(--tpl-primary);
            color: var(--tpl-secondary);
        }
        .tpl-field {
            background: color-mix(in srgb, var(--tpl-secondary) 96%, var(--tpl-dark));
            border-color: color-mix(in srgb, var(--tpl-dark) 18%, transparent);
            color: var(--tpl-dark);
        }
        .tpl-invert { background: var(--tpl-dark); color: var(--tpl-secondary); }

        @media (prefers-reduced-motion: reduce) { * { animation: none !important; transition: none !important; } }
    </style>
</head>
<body class="antialiased overflow-x-hidden" id="top">

@if($activePlaylist && $activePlaylist->items->isNotEmpty())
<div class="fixed bottom-5 right-5 z-50" x-data="{ playing: false }" x-init="audio = $refs.audio">
    <button @click="playing ? (audio.pause(), playing=false) : audio.play().then(()=>playing=true).catch(()=>{})"
        class="tpl-btn-solid w-10 h-10 flex items-center justify-center transition-opacity hover:opacity-90"
        :aria-label="playing ? 'Jeda musik' : 'Putar musik'">
        <span x-show="!playing" class="text-xs">▶</span>
        <span x-show="playing" class="text-xs">⏸</span>
    </button>
    <audio x-ref="audio" loop preload="none">
        <source src="{{ Storage::url($activePlaylist->items->first()->file_path) }}">
    </audio>
</div>
@endif

{{-- Opening --}}
@if($sections->where('section_key','opening')->first()?->is_enabled)
<div class="fixed inset-0 z-40 tpl-invert flex flex-col items-center justify-center text-center px-8"
    x-data="{opened:false}" x-show="!opened"
    x-transition:leave="transition duration-700 ease-in" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0">
    <p class="text-xs tracking-[0.4em] uppercase mb-6 opacity-40">Undangan Pernikahan</p>
    <h1 class="tpl-display text-4xl sm:text-5xl font-normal mb-2">{{ $wedding->bride_name }}</h1>
    <p class="tpl-accent text-xl tpl-display italic mb-2">&amp;</p>
    <h1 class="tpl-display text-4xl sm:text-5xl font-normal mb-8">{{ $wedding->groom_name }}</h1>
    @if($guest)<p class="text-sm mb-6 opacity-50">Kepada: {{ $guest->name }}</p>@endif
    <button @click="opened=true"
        class="px-8 py-3 border text-xs tracking-[0.2em] uppercase transition-colors"
        style="border-color: color-mix(in srgb, var(--tpl-secondary) 30%, transparent); color: var(--tpl-secondary);">
        Buka Undangan
    </button>
</div>
@endif

<div id="invitation-content">
    @include($templateService->sectionView($templateKey, 'hero'))

    @if($sections->where('section_key','couple')->first()?->is_enabled)
    @include($templateService->sectionView($templateKey, 'couple'))
    @endif

    @if($sections->where('section_key','quote')->first()?->is_enabled && $wedding->quote)
    @include($templateService->sectionView($templateKey, 'quote'))
    @endif

    @if($sections->where('section_key','love_story')->first()?->is_enabled)
    @include($templateService->sectionView($templateKey, 'love_story'))
    @endif

    @if($sections->where('section_key','countdown')->first()?->is_enabled && $wedding->date)
    @include($templateService->sectionView($templateKey, 'countdown'))
    @endif

    @if($sections->where('section_key','event')->first()?->is_enabled && $events->isNotEmpty())
    @include($templateService->sectionView($templateKey, 'event'))
    @endif

    @if($sections->where('section_key','venue')->first()?->is_enabled && $events->isNotEmpty())
    @include($templateService->sectionView($templateKey, 'venue'))
    @endif

    @if($sections->where('section_key','maps')->first()?->is_enabled)
    @include($templateService->sectionView($templateKey, 'maps'))
    @endif

    @if($sections->where('section_key','gallery')->first()?->is_enabled)
    @include($templateService->sectionView($templateKey, 'gallery'))
    @endif

    @if($sections->where('section_key','video')->first()?->is_enabled)
    @include($templateService->sectionView($templateKey, 'video'))
    @endif

    @if($sections->where('section_key','timeline')->first()?->is_enabled && $events->isNotEmpty())
    @include($templateService->sectionView($templateKey, 'timeline'))
    @endif

    @if($sections->where('section_key','rsvp')->first()?->is_enabled && $guest)
    @include($templateService->sectionView($templateKey, 'rsvp'))
    @endif

    @if($sections->where('section_key','guest_book')->first()?->is_enabled)
    @include($templateService->sectionView($templateKey, 'guest_book'))
    @endif

    @if($sections->where('section_key','gift')->first()?->is_enabled && $giftMethods->isNotEmpty())
    @include($templateService->sectionView($templateKey, 'gift'))
    @endif

    @if($sections->where('section_key','closing')->first()?->is_enabled)
    @include($templateService->sectionView($templateKey, 'closing'))
    @endif
</div>

</body>
</html>
