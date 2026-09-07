<section id="maps" class="py-20 px-6 bg-white">
    <div class="max-w-3xl mx-auto">
        <p class="text-[#c9a96e] text-xs tracking-[0.4em] uppercase mb-12 text-center reveal">Lokasi</p>
        @foreach($events->whereNotNull('maps_url') as $event)
        <div class="mb-8 reveal">
            <h3 class="font-display text-lg text-[#1a1a1a] mb-3 text-center">{{ $event->name }}</h3>
            <div class="aspect-video bg-stone-100 flex items-center justify-center">
                <a href="{{ $event->maps_url }}" target="_blank" rel="noopener" class="flex flex-col items-center gap-2 text-stone-400 hover:text-[#c9a96e] transition-colors">
                    <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                    <span class="text-sm tracking-wider">Buka di Google Maps</span>
                </a>
            </div>
        </div>
        @endforeach
    </div>
</section>
