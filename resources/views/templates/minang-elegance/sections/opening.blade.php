<section id="opening" class="fixed inset-0 z-50 flex flex-col items-center justify-center bg-[#1a0a0a] transition-opacity duration-700"
    x-data="{ opened: false }"
    x-show="!opened"
    x-transition:leave="opacity-0">

    <div class="text-center px-8 reveal">
        {{-- Ornament top --}}
        <div class="mb-8 opacity-60">
            <svg viewBox="0 0 120 20" class="w-32 mx-auto fill-[#c9a84c]">
                <path d="M60 2 L65 10 L70 2 L75 10 L80 2 L85 10 L90 2 L95 10 L100 2 L105 10 L110 2 L115 10 L120 2 L120 18 L0 18 L0 2 L5 10 L10 2 L15 10 L20 2 L25 10 L30 2 L35 10 L40 2 L45 10 L50 2 L55 10 Z"/>
            </svg>
        </div>

        <p class="text-[#c9a84c] text-xs tracking-[0.3em] uppercase mb-4 font-light">Undangan Pernikahan</p>

        <h1 class="font-serif text-4xl sm:text-5xl text-[#f5ede0] leading-tight mb-2">
            {{ $wedding->groom_nickname ?: $wedding->groom_name }}
        </h1>
        <p class="text-[#c9a84c] text-lg mb-2">&</p>
        <h1 class="font-serif text-4xl sm:text-5xl text-[#f5ede0] leading-tight mb-8">
            {{ $wedding->bride_nickname ?: $wedding->bride_name }}
        </h1>

        @if($guest)
        <p class="text-[#f5ede0]/60 text-sm mb-2">Kepada Yth.</p>
        <p class="text-[#f5ede0] text-lg font-medium mb-8">{{ $guest->name }}</p>
        @endif

        <button @click="opened = true"
            class="inline-flex items-center gap-2 px-8 py-3 border border-[#c9a84c]/60 text-[#c9a84c] text-sm tracking-widest uppercase hover:bg-[#c9a84c]/10 transition-colors">
            <span>Buka Undangan</span>
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M19 9l-7 7-7-7"/>
            </svg>
        </button>

        {{-- Ornament bottom --}}
        <div class="mt-8 opacity-60">
            <svg viewBox="0 0 120 20" class="w-32 mx-auto fill-[#c9a84c]">
                <path d="M60 18 L65 10 L70 18 L75 10 L80 18 L85 10 L90 18 L95 10 L100 18 L105 10 L110 18 L115 10 L120 18 L120 2 L0 2 L0 18 L5 10 L10 18 L15 10 L20 18 L25 10 L30 18 L35 10 L40 18 L45 10 L50 18 L55 10 Z"/>
            </svg>
        </div>
    </div>
</section>
