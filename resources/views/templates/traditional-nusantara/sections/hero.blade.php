@php $heroMedia=$media->where('collection','hero')->first(); @endphp
<section id="hero" class="relative min-h-screen flex flex-col items-center justify-center overflow-hidden bg-[#4a2c0a]">
    @if($heroMedia)
    <div class="absolute inset-0">
        <img src="{{ $heroMedia->url() }}" alt="{{ $wedding->coupleName() }}" class="w-full h-full object-cover opacity-25">
        <div class="absolute inset-0 bg-gradient-to-b from-[#4a2c0a]/70 via-transparent to-[#4a2c0a]/90"></div>
    </div>
    @else
    <div class="absolute inset-0 opacity-5" style="background-image:repeating-linear-gradient(45deg,#c9a84c 0,#c9a84c 1px,transparent 0,transparent 50%);background-size:20px 20px;"></div>
    @endif
    <div class="relative z-10 text-center px-6 max-w-lg mx-auto">
        <p class="text-[#c9a84c]/60 text-xs tracking-[0.4em] uppercase mb-8 reveal">Undangan Pernikahan</p>
        <h1 class="font-display text-5xl text-[#fdf5e4] font-light leading-tight reveal">
            {{ $wedding->bride_name }}<br>
            <span class="text-[#c9a84c] text-3xl">&</span><br>
            {{ $wedding->groom_name }}
        </h1>
        @if($wedding->date)
        <div class="mt-10 flex items-center justify-center gap-4 reveal">
            <span class="w-10 h-px bg-[#c9a84c]/40"></span>
            <time class="text-[#fdf5e4]/50 text-sm tracking-[0.15em]">{{ $wedding->date->translatedFormat('d F Y') }}</time>
            <span class="w-10 h-px bg-[#c9a84c]/40"></span>
        </div>
        @endif
        @if($wedding->venue)<p class="mt-3 text-[#fdf5e4]/30 text-sm reveal">{{ $wedding->venue }}</p>@endif
    </div>
    <div class="absolute bottom-8 left-1/2 -translate-x-1/2 animate-bounce">
        <svg class="w-5 h-5 text-[#c9a84c]/40" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M19 9l-7 7-7-7"/></svg>
    </div>
</section>
