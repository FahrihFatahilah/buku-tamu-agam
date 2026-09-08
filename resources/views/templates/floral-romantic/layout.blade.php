<!DOCTYPE html>
<html lang="id" class="scroll-smooth">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $wedding->bride_name }} & {{ $wedding->groom_name }} — Undangan Pernikahan</title>
    <meta name="description" content="{{ $wedding->seo_description ?? $wedding->description }}">
    @if($wedding->og_image)<meta property="og:image" content="{{ Storage::url($wedding->og_image) }}">@endif
    <link rel="canonical" href="{{ $wedding->publicUrl() }}">
    @if($wedding->favicon)<link rel="icon" href="{{ Storage::url($wedding->favicon) }}">@endif
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Libre+Baskerville:ital,wght@0,400;0,700;1,400&family=Open+Sans:wght@300;400&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        :root { --floral-rose: #b5606a; --floral-blush: #f9f0f0; --floral-sage: #7a9e7e; }
        body { font-family: 'Open Sans', sans-serif; background: var(--floral-blush); color: #2a1a1a; }
        .font-display { font-family: 'Libre Baskerville', serif; }
        .reveal { opacity: 0; transform: translateY(16px); transition: opacity 0.7s ease, transform 0.7s ease; }
        .reveal.visible { opacity: 1; transform: none; }
        @media (prefers-reduced-motion: reduce) { .reveal { opacity: 1; transform: none; transition: none; } }
    </style>
</head>
<body class="antialiased overflow-x-hidden">

@if($activePlaylist && $activePlaylist->items->isNotEmpty())
<div class="fixed bottom-5 right-5 z-50" x-data="{ playing: false, audio: null }" x-init="audio = $refs.audio">
    <button @click="playing ? (audio.pause(), playing=false) : audio.play().then(()=>playing=true).catch(()=>{})"
        class="w-10 h-10 rounded-full bg-[#b5606a] text-white flex items-center justify-center hover:bg-[#9a4f58] transition-colors text-xs">
        <span x-show="!playing">▶</span><span x-show="playing">⏸</span>
    </button>
    <audio x-ref="audio" loop preload="none">
        <source src="{{ Storage::url($activePlaylist->items->first()->file_path) }}">
    </audio>
</div>
@endif

@if($sections->where('section_key','opening')->first()?->is_enabled)
<div class="fixed inset-0 z-40 bg-[#f9f0f0] flex flex-col items-center justify-center text-center px-8"
    x-data="{opened:false}" x-show="!opened"
    x-transition:leave="transition duration-700" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0">
    <p class="text-[#b5606a]/60 text-xs tracking-[0.3em] uppercase mb-6">Undangan Pernikahan</p>
    <h1 class="font-display text-4xl text-[#2a1a1a] italic mb-1">{{ $wedding->groom_nickname ?: $wedding->groom_name }}</h1>
    <p class="text-[#b5606a] text-2xl mb-1">&</p>
    <h1 class="font-display text-4xl text-[#2a1a1a] italic mb-8">{{ $wedding->bride_nickname ?: $wedding->bride_name }}</h1>
    @if($guest)<p class="text-[#2a1a1a]/50 text-sm mb-6">Kepada: {{ $guest->name }}</p>@endif
    <button @click="opened=true"
        class="px-8 py-3 border border-[#b5606a]/40 text-[#b5606a] text-xs tracking-widest uppercase hover:border-[#b5606a] transition-colors">
        Buka Undangan
    </button>
</div>
@endif

<div id="invitation-content">
    @include($templateService->sectionView($templateKey, 'hero'))
    @if($sections->where('section_key','couple')->first()?->is_enabled) @include($templateService->sectionView($templateKey, 'couple')) @endif
    @if($sections->where('section_key','quote')->first()?->is_enabled && $wedding->quote) @include($templateService->sectionView($templateKey, 'quote')) @endif
    @if($sections->where('section_key','countdown')->first()?->is_enabled && $wedding->date) @include($templateService->sectionView($templateKey, 'countdown')) @endif
    @if($sections->where('section_key','event')->first()?->is_enabled && $events->isNotEmpty()) @include($templateService->sectionView($templateKey, 'event')) @endif
    @if($sections->where('section_key','venue')->first()?->is_enabled && $events->isNotEmpty()) @include($templateService->sectionView($templateKey, 'venue')) @endif
    @if($sections->where('section_key','maps')->first()?->is_enabled) @include($templateService->sectionView($templateKey, 'maps')) @endif
    @if($sections->where('section_key','gallery')->first()?->is_enabled) @include($templateService->sectionView($templateKey, 'gallery')) @endif
    @if($sections->where('section_key','rsvp')->first()?->is_enabled && $guest) @include($templateService->sectionView($templateKey, 'rsvp')) @endif
    @if($sections->where('section_key','guest_book')->first()?->is_enabled) @include($templateService->sectionView($templateKey, 'guest_book')) @endif
    @if($sections->where('section_key','gift')->first()?->is_enabled && $giftMethods->isNotEmpty()) @include($templateService->sectionView($templateKey, 'gift')) @endif
    @if($sections->where('section_key','closing')->first()?->is_enabled) @include($templateService->sectionView($templateKey, 'closing')) @endif
</div>

</body>
</html>
