<section id="hero" class="relative min-h-[80vh] flex items-center justify-center px-6 py-24 tpl-panel">
    @php $heroMedia = $media->firstWhere('collection', 'hero'); @endphp
    @if($heroMedia)
    <div class="absolute inset-0">
        <img src="{{ Storage::url($heroMedia->file_path) }}" alt="{{ $heroMedia->alt_text ?: $wedding->coupleName() }}"
            class="w-full h-full object-cover">
        <div class="absolute inset-0 bg-black/45"></div>
    </div>
    @endif
    <div class="relative text-center {{ $heroMedia ? 'text-white' : 'tpl-ink' }}">
        <p class="text-xs tracking-[0.4em] uppercase mb-6 {{ $heroMedia ? 'text-white/70' : 'tpl-faint' }}">The Wedding Of</p>
        <h1 class="tpl-display text-4xl sm:text-6xl font-normal leading-tight">
            {{ $wedding->bride_nickname ?: $wedding->bride_name }}
            <span class="block text-2xl sm:text-3xl italic my-2 {{ $heroMedia ? 'text-white/60' : 'tpl-muted' }}">&amp;</span>
            {{ $wedding->groom_nickname ?: $wedding->groom_name }}
        </h1>
        @if($wedding->date)
        <p class="mt-8 text-sm tracking-widest {{ $heroMedia ? 'text-white/80' : 'tpl-muted' }}">
            {{ $wedding->date->translatedFormat('d F Y') }}
        </p>
        @endif
        @if($wedding->venue)
        <p class="mt-1 text-sm {{ $heroMedia ? 'text-white/60' : 'tpl-faint' }}">{{ $wedding->venue }}</p>
        @endif
    </div>
</section>
