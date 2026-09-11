@php
    $p = $node['props'] ?? [];
    $showDress = $p['showDressCode'] ?? true;
@endphp
@if($events->isNotEmpty())
<div class="n-inner">
    <div class="text-center mb-12">
        @if(!empty($p['eyebrow']))
        <p class="text-xs tracking-[0.3em] uppercase opacity-60 mb-3" data-edit-prop="eyebrow">{{ $p['eyebrow'] }}</p>
        @endif
        @if(!empty($p['heading']))
        <h2 class="n-display text-3xl" data-edit-prop="heading">{{ $p['heading'] }}</h2>
        @endif
    </div>

    <div class="space-y-4">
        @foreach($events as $event)
        <div class="p-6" style="border: 1px solid color-mix(in srgb, currentColor 15%, transparent);">
            <p class="n-display text-xl">{{ $event->name }}</p>

            @if($event->starts_at)
            <p class="text-sm opacity-70 mt-2">
                {{ $event->starts_at->translatedFormat('l, d F Y') }}
                &middot; {{ $event->starts_at->format('H:i') }}@if($event->ends_at)&ndash;{{ $event->ends_at->format('H:i') }} WIB @endif
            </p>
            @endif

            @if($event->venue)
            <p class="text-sm mt-2">{{ $event->venue }}</p>
            @endif

            @if($event->address)
            <p class="text-xs opacity-60 mt-1">{{ $event->address }}</p>
            @endif

            @if($showDress && $event->dress_code)
            <p class="text-xs opacity-70 mt-2">Dress code: {{ $event->dress_code }}</p>
            @endif
        </div>
        @endforeach
    </div>
</div>
@endif
