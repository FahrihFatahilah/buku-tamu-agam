<section id="venue" class="py-20 px-6 bg-white">
    <div class="max-w-2xl mx-auto text-center">
        <p class="text-[#c9a96e] text-xs tracking-[0.4em] uppercase mb-3 reveal">Lokasi</p>
        <h2 class="font-display text-3xl text-[#1a1a1a] mb-12 reveal">Tempat Acara</h2>
        @foreach($events as $event)
        <div class="mb-8 reveal">
            <h3 class="font-display text-xl text-[#1a1a1a] mb-1">{{ $event->name }}</h3>
            @if($event->starts_at)
            <p class="text-[#c9a96e] text-sm font-medium mt-1">{{ $event->starts_at->translatedFormat('l, d F Y') }}</p>
            <p class="text-stone-400 text-sm">
                {{ $event->starts_at->format('H:i') }}
                @if($event->ends_at) — {{ $event->ends_at->format('H:i') }} WIB @endif
            </p>
            @endif
            @if($event->venue)
            <p class="text-stone-700 font-medium mt-2">{{ $event->venue }}</p>
            @endif
            @if($event->address)
            <p class="text-stone-400 text-sm mt-1">{{ $event->address }}</p>
            @endif
            @if($event->maps_url)
            <a href="{{ $event->maps_url }}" target="_blank" rel="noopener"
                class="inline-flex items-center gap-2 mt-3 text-sm text-[#c9a96e] border border-[#c9a96e]/30 px-4 py-2 hover:bg-[#c9a96e]/5 transition-colors">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                Google Maps
            </a>
            @endif
        </div>
        @if(!$loop->last)
        <div class="w-px h-8 bg-[#c9a96e]/20 mx-auto mb-8"></div>
        @endif
        @endforeach
    </div>
</section>
