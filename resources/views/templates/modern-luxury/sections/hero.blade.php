@php
    $heroMedia = $media->where('collection', 'hero')->first();
@endphp
<section id="hero" class="relative min-h-screen flex flex-col items-center justify-center overflow-hidden bg-[#1a1a1a]">
    @if($heroMedia)
    <div class="absolute inset-0">
        <img src="{{ $heroMedia->url() }}" alt="{{ $wedding->coupleName() }}" class="w-full h-full object-cover opacity-30">
        <div class="absolute inset-0 bg-gradient-to-b from-black/60 via-black/20 to-black/80"></div>
    </div>
    @endif
    <div class="relative z-10 text-center px-6 max-w-lg mx-auto">
        <p class="text-[#c9a96e] text-xs tracking-[0.5em] uppercase mb-10 reveal">The Wedding of</p>
        <h1 class="font-display text-5xl sm:text-6xl text-white font-light leading-tight reveal">
            {{ $wedding->bride_name }}<br>
            <span class="text-[#c9a96e] text-3xl italic font-light">&</span><br>
            {{ $wedding->groom_name }}
        </h1>
        @if($wedding->date)
        <div class="mt-10 flex items-center justify-center gap-4 reveal">
            <span class="w-12 h-px bg-[#c9a96e]/40"></span>
            <time class="text-white/50 text-sm tracking-[0.2em]">{{ $wedding->date->translatedFormat('d F Y') }}</time>
            <span class="w-12 h-px bg-[#c9a96e]/40"></span>
        </div>
        @endif
        @if($wedding->venue)
        <p class="mt-3 text-white/30 text-sm tracking-wider reveal">{{ $wedding->venue }}</p>
        @endif
    </div>
    <div class="absolute bottom-8 left-1/2 -translate-x-1/2 animate-bounce">
        <svg class="w-5 h-5 text-white/30" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M19 9l-7 7-7-7"/>
        </svg>
    </div>
</section>
