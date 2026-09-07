@php $heroMedia=$media->where('collection','hero')->first(); @endphp
<section id="hero" class="relative min-h-screen flex flex-col items-center justify-center overflow-hidden bg-[#f9f0f0]">
    @if($heroMedia)
    <div class="absolute inset-0">
        <img src="{{ $heroMedia->url() }}" alt="{{ $wedding->coupleName() }}" class="w-full h-full object-cover opacity-25">
        <div class="absolute inset-0 bg-gradient-to-b from-[#f9f0f0]/60 via-transparent to-[#f9f0f0]/80"></div>
    </div>
    @endif
    {{-- Floral ornament top --}}
    <div class="absolute top-0 left-0 right-0 h-24 opacity-20" style="background: radial-gradient(ellipse at 20% 0%, #b5606a 0%, transparent 60%), radial-gradient(ellipse at 80% 0%, #7a9e7e 0%, transparent 60%);"></div>
    <div class="relative z-10 text-center px-6 max-w-lg mx-auto">
        <p class="text-[#b5606a]/60 text-xs tracking-[0.4em] uppercase mb-8 reveal">Undangan Pernikahan</p>
        <h1 class="font-display text-5xl text-[#2a1a1a] italic leading-tight reveal">
            {{ $wedding->bride_name }}<br>
            <span class="text-[#b5606a] text-3xl not-italic">&</span><br>
            {{ $wedding->groom_name }}
        </h1>
        @if($wedding->date)
        <div class="mt-10 flex items-center justify-center gap-4 reveal">
            <span class="w-10 h-px bg-[#b5606a]/30"></span>
            <time class="text-[#2a1a1a]/50 text-sm tracking-[0.15em]">{{ $wedding->date->translatedFormat('d F Y') }}</time>
            <span class="w-10 h-px bg-[#b5606a]/30"></span>
        </div>
        @endif
        @if($wedding->venue)<p class="mt-3 text-[#2a1a1a]/40 text-sm reveal">{{ $wedding->venue }}</p>@endif
    </div>
    <div class="absolute bottom-8 left-1/2 -translate-x-1/2 animate-bounce">
        <svg class="w-5 h-5 text-[#b5606a]/40" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M19 9l-7 7-7-7"/></svg>
    </div>
</section>
