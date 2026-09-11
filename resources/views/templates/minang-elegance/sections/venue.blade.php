@php
    $venueSection = $sections->firstWhere('section_key', 'venue');
    $firstEvent   = $events->first();

    $mapsUrl = $wedding->maps_url
        ?: ($wedding->latitude && $wedding->longitude
            ? 'https://www.google.com/maps/search/?api=1&query=' . $wedding->latitude . ',' . $wedding->longitude
            : ($firstEvent?->maps_url ?: null));
@endphp

<section id="venue" class="section-bg relative py-20 px-6">

    @include('templates._section-bg', ['section' => $venueSection, 'defaultBg' => '#2C1810'])

    <div class="section-content max-w-xl mx-auto text-center">
        <div class="reveal">
            <p class="text-[#B8960C] text-xs tracking-[0.3em] uppercase mb-3">Lokasi</p>
            <h2 class="font-serif text-[#F5F0E8] text-2xl mb-8">Tempat Acara</h2>
        </div>

        <div class="reveal">
            @if($wedding->venue)
            <p class="text-[#F5F0E8] text-lg font-serif">{{ $wedding->venue }}</p>
            @elseif($firstEvent?->venue)
            <p class="text-[#F5F0E8] text-lg font-serif">{{ $firstEvent->venue }}</p>
            @endif

            @if($wedding->address)
            <p class="text-[#F5F0E8]/50 text-sm mt-3 leading-relaxed">{{ $wedding->address }}</p>
            @elseif($firstEvent?->address)
            <p class="text-[#F5F0E8]/50 text-sm mt-3 leading-relaxed">{{ $firstEvent->address }}</p>
            @endif
        </div>

        @if($mapsUrl)
        <div class="reveal mt-8">
            <a href="{{ $mapsUrl }}" target="_blank" rel="noopener"
                class="inline-flex items-center gap-2 text-xs text-[#B8960C] border border-[#B8960C]/40 px-4 py-2.5 tracking-wider hover:bg-[#B8960C]/10 transition-colors">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/>
                </svg>
                Buka di Google Maps
            </a>
        </div>
        @endif

        <span class="ornament-line w-8 h-px bg-[#B8960C]/40 block mx-auto mt-10"></span>
    </div>
</section>
