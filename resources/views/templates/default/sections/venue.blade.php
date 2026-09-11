@php $t = $text ?? []; @endphp
@if($events->isNotEmpty())
<section id="venue" class="py-20 px-6 tpl-panel">
    <div class="max-w-lg mx-auto text-center">
        <p class="text-xs tracking-[0.3em] uppercase tpl-faint mb-3" data-edit="eyebrow">{{ $t['eyebrow'] ?? 'Lokasi' }}</p>
        <h2 class="tpl-display text-3xl tpl-ink mb-6" data-edit="heading">{{ $t['heading'] ?? 'Tempat Acara' }}</h2>

        @php
            $firstEvent = $events->first();
            $mapsUrl = $wedding->maps_url
                ?: ($wedding->latitude && $wedding->longitude
                    ? 'https://www.google.com/maps/search/?api=1&query=' . $wedding->latitude . ',' . $wedding->longitude
                    : ($firstEvent?->maps_url ?: null));
        @endphp

        @if($wedding->venue)
        <p class="tpl-ink">{{ $wedding->venue }}</p>
        @elseif($firstEvent?->venue)
        <p class="tpl-ink">{{ $firstEvent->venue }}</p>
        @endif
        @if($wedding->address)
        <p class="text-sm tpl-muted mt-2">{{ $wedding->address }}</p>
        @elseif($firstEvent?->address)
        <p class="text-sm tpl-muted mt-2">{{ $firstEvent->address }}</p>
        @endif

        @if($mapsUrl)
        <a href="{{ $mapsUrl }}" target="_blank" rel="noopener" data-edit="cta"
            class="tpl-btn inline-block mt-8 px-6 py-3 border text-xs tracking-[0.2em] uppercase transition-colors">
            {{ $t['cta'] ?? 'Buka Google Maps' }}
        </a>
        @endif
    </div>
</section>
@endif
