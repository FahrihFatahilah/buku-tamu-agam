@php $p = $node['props'] ?? []; @endphp
<div class="n-inner">
    @if(!empty($p['eyebrow']))
    <p class="text-xs tracking-[0.3em] uppercase opacity-60 mb-6" data-edit-prop="eyebrow">{{ $p['eyebrow'] }}</p>
    @endif

    @if($p['showCouple'] ?? true)
    <p class="n-display text-2xl sm:text-3xl mb-6">{{ $wedding->coupleName() }}</p>
    @endif

    @if(!empty($p['body']))
    <p class="text-sm leading-relaxed opacity-70" data-edit-prop="body">{{ $p['body'] }}</p>
    @endif

    @if($wedding->date)
    <p class="text-xs mt-8 tracking-widest opacity-60">{{ $wedding->date->translatedFormat('d F Y') }}</p>
    @endif
</div>
