@php
    $p = $node['props'] ?? [];
    $items = collect($p['items'] ?? []);
@endphp
@if($items->isNotEmpty())
<div class="n-inner">
    <div class="text-center mb-12">
        @if(!empty($p['eyebrow']))
        <p class="text-xs tracking-[0.3em] uppercase opacity-60 mb-3" data-edit-prop="eyebrow">{{ $p['eyebrow'] }}</p>
        @endif
        @if(!empty($p['heading']))
        <h2 class="n-display text-3xl" data-edit-prop="heading">{{ $p['heading'] }}</h2>
        @endif
    </div>

    <div class="space-y-8">
        @foreach($items as $item)
        <div class="flex gap-6">
            <p class="w-16 shrink-0 text-sm opacity-70 tabular-nums">{{ $item['time'] ?? '' }}</p>
            <div class="pl-6 pb-2" style="border-left: 1px solid color-mix(in srgb, currentColor 20%, transparent);">
                <p class="n-display text-lg">{{ $item['title'] ?? '' }}</p>
                @if(!empty($item['description']))
                <p class="text-sm opacity-70 mt-1 leading-relaxed">{{ $item['description'] }}</p>
                @endif
            </div>
        </div>
        @endforeach
    </div>
</div>
@endif
