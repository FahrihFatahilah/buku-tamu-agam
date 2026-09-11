@php
    $mapsUrl = $wedding->maps_url
        ?: ($wedding->latitude && $wedding->longitude
            ? 'https://www.google.com/maps/search/?api=1&query=' . $wedding->latitude . ',' . $wedding->longitude
            : null);
@endphp
@if($mapsUrl)
<section class="py-16 px-6 bg-white">
    <div class="max-w-2xl mx-auto text-center">
        <a href="{{ $mapsUrl }}" target="_blank" rel="noopener"
            class="inline-block px-6 py-3 border border-stone-300 text-xs tracking-[0.2em] uppercase text-stone-600 hover:border-stone-500 transition-colors">
            Lihat Lokasi di Google Maps
        </a>
    </div>
</section>
@endif
