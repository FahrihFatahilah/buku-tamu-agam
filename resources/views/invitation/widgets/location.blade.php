@php
    $p = $node['props'] ?? [];
    $firstEvent = $events->first();

    $venue = $wedding->venue ?: $firstEvent?->venue;
    $address = $wedding->address ?: $firstEvent?->address;

    $mapsUrl = $wedding->maps_url
        ?: ($wedding->latitude && $wedding->longitude
            ? 'https://www.google.com/maps/search/?api=1&query=' . $wedding->latitude . ',' . $wedding->longitude
            : ($firstEvent?->maps_url ?: null));
@endphp
<div class="n-inner">
    @if(!empty($p['eyebrow']))
    <p class="text-xs tracking-[0.3em] uppercase opacity-60 mb-3" data-edit-prop="eyebrow">{{ $p['eyebrow'] }}</p>
    @endif

    @if(!empty($p['heading']))
    <h2 class="n-display text-3xl mb-6" data-edit-prop="heading">{{ $p['heading'] }}</h2>
    @endif

    @if($venue)
    <p class="text-lg n-display">{{ $venue }}</p>
    @endif

    @if($address)
    <p class="text-sm opacity-70 mt-2">{{ $address }}</p>
    @endif

    @if(($p['showButton'] ?? true) && $mapsUrl)
    <a href="{{ $mapsUrl }}" target="_blank" rel="noopener noreferrer"
        class="inline-block mt-8 px-6 py-3 text-xs tracking-[0.2em] uppercase"
        style="border: 1px solid var(--n-accent, #B8960C); color: var(--n-accent, #B8960C);"
        data-edit-prop="buttonLabel">{{ $p['buttonLabel'] ?? 'Buka Google Maps' }}</a>
    @endif
</div>
