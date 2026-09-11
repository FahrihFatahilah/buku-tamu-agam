@php
    $p = $node['props'] ?? [];
    $collections = is_array($p['collections'] ?? null) ? $p['collections'] : ['gallery', 'prewedding'];
    $columns = max(1, min(4, (int) ($p['columns'] ?? 3)));
    $items = $media->whereIn('collection', $collections)->values();
@endphp
<div class="n-inner">
    <div class="text-center mb-12">
        @if(!empty($p['eyebrow']))
        <p class="text-xs tracking-[0.3em] uppercase opacity-60 mb-3" data-edit-prop="eyebrow">{{ $p['eyebrow'] }}</p>
        @endif
        @if(!empty($p['heading']))
        <h2 class="n-display text-3xl" data-edit-prop="heading">{{ $p['heading'] }}</h2>
        @endif
    </div>

    @if($items->isNotEmpty())
    <div class="n-grid" data-n-grid="{{ $columns }}"
        style="display: grid; grid-template-columns: repeat({{ $columns }}, minmax(0, 1fr));">
        @foreach($items as $item)
        <div class="aspect-square overflow-hidden">
            <img src="{{ Storage::url($item->file_path) }}"
                alt="{{ $item->alt_text ?: $wedding->coupleName() }}"
                class="w-full h-full object-cover" loading="lazy" decoding="async">
        </div>
        @endforeach
    </div>
    @else
    <p class="text-center text-sm opacity-60">Belum ada foto.</p>
    @endif
</div>
