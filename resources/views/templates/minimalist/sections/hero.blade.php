@php $heroMedia = $media->where('collection','hero')->first(); @endphp
<section id="hero" class="relative min-h-screen flex flex-col items-center justify-center overflow-hidden" style="background:#111;">
    @if($heroMedia)
    <div class="absolute inset-0">
        <img src="{{ $heroMedia->url() }}" alt="{{ $wedding->coupleName() }}" class="w-full h-full object-cover opacity-20">
        <div class="absolute inset-0 bg-gradient-to-b from-black/50 to-black/80"></div>
    </div>
    @endif
    <div class="relative z-10 text-center px-6 max-w-lg mx-auto">
        <p class="text-stone-400 text-xs tracking-[0.5em] uppercase mb-10">Undangan Pernikahan</p>
        <h1 class="font-display text-5xl text-white font-normal leading-tight">
            {{ $wedding->bride_name }}<br>
            <span class="text-stone-400 text-2xl font-light">&</span><br>
            {{ $wedding->groom_name }}
        </h1>
        @if($wedding->date)
        <div class="mt-10 flex items-center justify-center gap-4">
            <span class="w-10 h-px bg-stone-600"></span>
            <time class="text-stone-400 text-sm tracking-[0.2em]">{{ $wedding->date->translatedFormat('d F Y') }}</time>
            <span class="w-10 h-px bg-stone-600"></span>
        </div>
        @endif
        @if($wedding->venue)<p class="mt-3 text-stone-500 text-sm">{{ $wedding->venue }}</p>@endif
    </div>
    <div class="absolute bottom-8 left-1/2 -translate-x-1/2 animate-bounce">
        <svg class="w-5 h-5 text-stone-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M19 9l-7 7-7-7"/></svg>
    </div>
</section>
