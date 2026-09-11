@php $firstEvent = $events->first(); @endphp
@if($firstEvent)
<section class="py-20 px-6 bg-stone-50">
    <div class="max-w-lg mx-auto text-center">
        <p class="text-xs tracking-[0.3em] uppercase text-stone-400 mb-3">Lokasi</p>
        <h2 class="font-display text-3xl text-stone-800 mb-6">Tempat Acara</h2>

        @if($wedding->venue)
        <p class="text-stone-700">{{ $wedding->venue }}</p>
        @endif
        @if($wedding->address)
        <p class="text-sm text-stone-500 mt-2">{{ $wedding->address }}</p>
        @endif
        @if($firstEvent->venue && $firstEvent->venue !== $wedding->venue)
        <p class="text-sm text-stone-500 mt-4">{{ $firstEvent->venue }}</p>
        @endif

        @php
            $mapsUrl = $wedding->maps_url
                ?: ($wedding->latitude && $wedding->longitude
                    ? 'https://www.google.com/maps/search/?api=1&query=' . $wedding->latitude . ',' . $wedding->longitude
                    : null);
        @endphp
        @if($mapsUrl)
        <a href="{{ $mapsUrl }}" target="_blank" rel="noopener"
            class="inline-block mt-8 px-6 py-3 border border-stone-300 text-xs tracking-[0.2em] uppercase text-stone-600 hover:border-stone-500 transition-colors">
            Buka Google Maps
        </a>
        @endif
    </div>
</section>
@endif
