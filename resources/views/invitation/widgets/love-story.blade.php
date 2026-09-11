@php
    $p = $node['props'] ?? [];
    $stories = collect($p['stories'] ?? []);
@endphp
@if($stories->isNotEmpty())
<div class="n-inner">
    <div class="text-center mb-12">
        @if(!empty($p['eyebrow']))
        <p class="text-xs tracking-[0.3em] uppercase opacity-60 mb-3" data-edit-prop="eyebrow">{{ $p['eyebrow'] }}</p>
        @endif
        @if(!empty($p['heading']))
        <h2 class="n-display text-3xl" data-edit-prop="heading">{{ $p['heading'] }}</h2>
        @endif
    </div>

    <div class="space-y-10">
        @foreach($stories as $story)
        <div class="pl-6" style="border-left: 1px solid color-mix(in srgb, currentColor 20%, transparent);">
            @if(!empty($story['year']))
            <p class="text-xs tracking-widest uppercase" style="color: var(--n-accent, #B8960C);">{{ $story['year'] }}</p>
            @endif
            @if(!empty($story['title']))
            <p class="n-display text-xl mt-1">{{ $story['title'] }}</p>
            @endif
            @if(!empty($story['description']))
            <p class="text-sm opacity-70 mt-2 leading-relaxed">{{ $story['description'] }}</p>
            @endif
        </div>
        @endforeach
    </div>
</div>
@endif
