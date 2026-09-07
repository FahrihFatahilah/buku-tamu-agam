<section id="venue" class="py-20 px-6 bg-white">
    <div class="max-w-2xl mx-auto text-center">
        <p class="text-stone-400 text-xs tracking-[0.4em] uppercase mb-3 reveal">Lokasi</p>
        <h2 class="font-display text-3xl text-stone-900 mb-12 reveal">Tempat Acara</h2>
        @foreach($events as $event)
        <div class="mb-8 reveal">
            <h3 class="font-display text-xl text-stone-900 mb-1">{{ $event->name }}</h3>
            @if($event->starts_at)
            <p class="text-stone-500 text-sm mt-1">{{ $event->starts_at->translatedFormat('l, d F Y') }}</p>
            <p class="text-stone-400 text-sm">{{ $event->starts_at->format('H:i') }}@if($event->ends_at) — {{ $event->ends_at->format('H:i') }} WIB@endif</p>
            @endif
            @if($event->venue)<p class="text-stone-700 font-medium mt-2">{{ $event->venue }}</p>@endif
            @if($event->address)<p class="text-stone-400 text-sm mt-1">{{ $event->address }}</p>@endif
            @if($event->maps_url)<a href="{{ $event->maps_url }}" target="_blank" rel="noopener" class="inline-block mt-3 text-xs text-stone-400 hover:text-stone-600 underline underline-offset-2">Buka di Google Maps →</a>@endif
        </div>
        @if(!$loop->last)<div class="w-px h-6 bg-stone-200 mx-auto mb-8"></div>@endif
        @endforeach
    </div>
</section>
