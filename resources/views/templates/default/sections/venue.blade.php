@php $firstEvent = $events->first(); @endphp
@if($firstEvent)
<section class="py-20 px-6 tpl-panel">
    <div class="max-w-lg mx-auto text-center">
        <p class="text-xs tracking-[0.3em] uppercase tpl-faint mb-3">Lokasi</p>
        <h2 class="tpl-display text-3xl tpl-ink mb-6">Tempat Acara</h2>

        @if($wedding->venue)
        <p class="tpl-ink">{{ $wedding->venue }}</p>
        @endif
        @if($wedding->address)
        <p class="text-sm tpl-muted mt-2">{{ $wedding->address }}</p>
        @endif
        @if($firstEvent->venue && $firstEvent->venue !== $wedding->venue)
        <p class="text-sm tpl-muted mt-4">{{ $firstEvent->venue }}</p>
        @endif

        @php
            $mapsUrl = $wedding->maps_url
                ?: ($wedding->latitude && $wedding->longitude
                    ? 'https://www.google.com/maps/search/?api=1&query=' . $wedding->latitude . ',' . $wedding->longitude
                    : null);
        @endphp
        @if($mapsUrl)
        <a href="{{ $mapsUrl }}" target="_blank" rel="noopener"
            class="tpl-btn inline-block mt-8 px-6 py-3 border text-xs tracking-[0.2em] uppercase transition-colors">
            Buka Google Maps
        </a>
        @endif
    </div>
</section>
@endif
