@php $p = $node['props'] ?? []; @endphp
@if($wedding->quote)
<div class="n-inner text-center">
    <div class="w-10 h-px mx-auto mb-10" style="background: currentColor; opacity: .25;"></div>
    <p class="n-display text-xl sm:text-2xl italic leading-relaxed">{{ $wedding->quote }}</p>
    @if(($p['showSource'] ?? true) && !empty($wedding->settings['quote_source']))
    <p class="text-xs tracking-widest uppercase opacity-60 mt-6">{{ $wedding->settings['quote_source'] }}</p>
    @endif
    <div class="w-10 h-px mx-auto mt-10" style="background: currentColor; opacity: .25;"></div>
</div>
@endif
