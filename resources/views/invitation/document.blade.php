{{--
    Public document renderer.

    This is the SAME view used by the editor canvas and by the public
    invitation, so what the admin sees is what visitors get.

    Deliberately loads only the base app CSS/JS — never any builder asset.
--}}
@php
    $headingFont = $theme['typography']['headingFont'] ?? 'Playfair Display';
    $bodyFont = $theme['typography']['bodyFont'] ?? 'Lato';
    $gfont = fn ($f) => str_replace(' ', '+', trim((string) $f));
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

    @unless($isEditor)
    <meta name="robots" content="index,follow">
    @endunless

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family={{ $gfont($headingFont) }}&family={{ $gfont($bodyFont) }}&display=swap" rel="stylesheet">

    @vite(['resources/css/app.css', 'resources/css/animations.css', 'resources/js/app.js'])

    <style>
        /* Theme tokens + per-node compiled styles (server-rendered, whitelisted). */
        {!! $compiledStyles !!}

        html, body { margin: 0; padding: 0; }
        body {
            font-family: var(--n-font-body, sans-serif);
            color: var(--n-text, #2C1810);
            background: var(--n-background, #fff);
            overflow-x: hidden;
        }
        .n-display { font-family: var(--n-font-display, serif); }

        /* Overlay layers never intercept taps. */
        .n-overlay { position: fixed; inset: 0; pointer-events: none; overflow: hidden; }

        /* Per-section colour veil, driven by overlayColor/overlayOpacity styles. */
        .n-veil {
            position: absolute; inset: 0; pointer-events: none;
            background: var(--n-overlay-color, transparent);
            opacity: var(--n-overlay-opacity, 0);
        }

        /* Content sits above the veil. */
        .n-inner { position: relative; z-index: 1; }
        .n-inner > :first-child { margin-top: 0; }
        .n-inner > :last-child { margin-bottom: 0; }

        /* Layout helpers for the layout widgets. */
        .n-flex { display: flex; }
        .n-col { min-width: 0; }

        /* Grids collapse gracefully on small screens. */
        @media (max-width: 640px) {
            .n-grid[data-n-grid="2"] { grid-template-columns: repeat(1, minmax(0, 1fr)) !important; }
            .n-grid[data-n-grid="3"] { grid-template-columns: repeat(2, minmax(0, 1fr)) !important; }
            .n-grid[data-n-grid="4"] { grid-template-columns: repeat(2, minmax(0, 1fr)) !important; }
        }

        /* Decorations are absolutely positioned within their parent. */
        [data-node-type="decoration"] { pointer-events: none; }
        [data-node-type="decoration"] img,
        [data-node-type="decoration"] svg { width: 100%; height: auto; display: block; }

        @media (prefers-reduced-motion: reduce) {
            *, *::before, *::after {
                animation: none !important;
                transition: none !important;
            }
            [data-anim-entrance] { opacity: 1 !important; transform: none !important; }
        }
    </style>

    @if($isEditor)
    {{-- Editor-only affordances; never emitted on the public page. --}}
    <style>
        [data-node-id] { position: relative; }
        [data-node-id]:hover { outline: 1px dashed rgba(59,130,246,.6); outline-offset: -1px; }
        [data-node-id].is-selected { outline: 2px solid rgba(59,130,246,.95); outline-offset: -2px; }
    </style>
    @endif
</head>
<body class="antialiased overflow-x-hidden" id="top">

@if(!empty($overlaysBehindHtml))
<div class="n-overlay" aria-hidden="true">{!! $overlaysBehindHtml !!}</div>
@endif

@if($activePlaylist && $activePlaylist->items->isNotEmpty())
<div class="fixed bottom-5 right-5 z-[200]" x-data="{ playing: false }" x-init="audio = $refs.audio">
    <button type="button"
        @click="playing ? (audio.pause(), playing=false) : audio.play().then(()=>playing=true).catch(()=>{})"
        class="w-10 h-10 rounded-full flex items-center justify-center shadow-lg"
        style="background: var(--n-primary, #7C3238); color: var(--n-secondary, #F5F0E8);"
        :aria-label="playing ? 'Jeda musik' : 'Putar musik'">
        <span x-show="!playing" class="text-xs">▶</span>
        <span x-show="playing" class="text-xs">⏸</span>
    </button>
    <audio x-ref="audio" :loop="true" preload="none">
        <source src="{{ Storage::url($activePlaylist->items->first()->file_path) }}">
    </audio>
</div>
@endif

<main id="invitation-content">
    {!! $nodesHtml !!}
</main>

@if(!empty($overlaysFrontHtml))
<div class="n-overlay" aria-hidden="true">{!! $overlaysFrontHtml !!}</div>
@endif

</body>
</html>
