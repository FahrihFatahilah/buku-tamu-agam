@php
    $p = $node['props'] ?? [];
    $heroMedia = ! empty($p['heroImage']) ? $media->firstWhere('collection', 'hero') : null;
    $useNick = $p['useNickname'] ?? true;

    $bride = $useNick ? ($wedding->bride_nickname ?: $wedding->bride_name) : $wedding->bride_name;
    $groom = $useNick ? ($wedding->groom_nickname ?: $wedding->groom_name) : $wedding->groom_name;

    $hasImage = (bool) ($heroMedia && $heroMedia->file_path);
@endphp

@if($hasImage)
<img src="{{ Storage::url($heroMedia->file_path) }}"
    alt="{{ $heroMedia->alt_text ?: $wedding->coupleName() }}"
    class="absolute inset-0 w-full h-full object-cover" style="z-index: 0;" fetchpriority="high">
<div class="absolute inset-0 bg-black/45" style="z-index: 1;" aria-hidden="true"></div>
@endif

<div class="n-inner relative flex flex-col items-center justify-center text-center px-6 py-20"
    style="min-height: inherit; @if($hasImage) color: #fff; @endif">

    @if(!empty($p['eyebrow']))
    <p class="text-xs tracking-[0.4em] uppercase mb-6 {{ $hasImage ? 'text-white/70' : '' }}"
        data-edit-prop="eyebrow">{{ $p['eyebrow'] }}</p>
    @endif

    <h1 class="n-display text-4xl sm:text-6xl font-normal leading-tight">
        <span data-edit-prop="brideName">{{ $bride }}</span>
        <span class="block text-2xl sm:text-3xl italic my-2 {{ $hasImage ? 'text-white/60' : '' }}">&amp;</span>
        <span data-edit-prop="groomName">{{ $groom }}</span>
    </h1>

    @if(($p['showDate'] ?? true) && $wedding->date)
    <p class="mt-8 text-sm tracking-widest {{ $hasImage ? 'text-white/80' : '' }}">
        {{ $wedding->date->translatedFormat('d F Y') }}
    </p>
    @endif

    @if(($p['showVenue'] ?? true) && $wedding->venue)
    <p class="mt-1 text-sm {{ $hasImage ? 'text-white/60' : '' }}">{{ $wedding->venue }}</p>
    @endif
</div>
