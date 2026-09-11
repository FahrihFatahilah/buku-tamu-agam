@php
    $p = $node['props'] ?? [];
    $firstEvent = $events->first();

    $mapsUrl = $wedding->maps_url
        ?: ($wedding->latitude && $wedding->longitude
            ? 'https://www.google.com/maps/search/?api=1&query=' . $wedding->latitude . ',' . $wedding->longitude
            : ($firstEvent?->maps_url ?: null));

    $embed = $firstEvent?->maps_embed;
    $mode = $p['mode'] ?? 'button';
    $height = max(160, min(800, (int) ($p['height'] ?? 320)));
@endphp

@if($mode === 'embed' && $embed)
<div class="n-inner">
    <div style="height: {{ $height }}px; width: 100%; position: relative;">
        {!! preg_replace(
            ['/width="[^"]*"/', '/height="[^"]*"/'],
            ['width="100%"', 'height="100%"'],
            $embed
        ) !!}
    </div>
</div>
@elseif($mapsUrl)
<div class="n-inner">
    <a href="{{ $mapsUrl }}" target="_blank" rel="noopener noreferrer"
        class="inline-block px-6 py-3 text-xs tracking-[0.2em] uppercase"
        style="border: 1px solid var(--n-accent, #B8960C); color: var(--n-accent, #B8960C);"
        data-edit-prop="buttonLabel">{{ $p['buttonLabel'] ?? 'Lihat Lokasi di Google Maps' }}</a>
</div>
@endif
