@php
    $p = $node['props'] ?? [];
    $label = (string) ($p['label'] ?? '');
    $href = (string) ($p['href'] ?? '#');
    $target = ($p['target'] ?? '_self') === '_blank' ? '_blank' : '_self';
    $variant = $p['variant'] ?? 'solid';
    $variant = in_array($variant, ['solid', 'outline', 'ghost'], true) ? $variant : 'solid';

    // Only http(s), mailto, tel, or in-page anchors.
    $safe = preg_match('#^(https?://|mailto:|tel:|#)#i', $href) ? $href : '#';
@endphp
<a href="{{ $safe }}" @if($target === '_blank') target="_blank" rel="noopener noreferrer" @endif
    data-edit-prop="label"
    class="inline-block text-center transition-opacity hover:opacity-90"
    style="
        @if($variant === 'solid') background: var(--n-primary, #7C3238); color: var(--n-secondary, #F5F0E8);
        @elseif($variant === 'outline') border: 1px solid var(--n-primary, #7C3238); color: var(--n-primary, #7C3238); background: transparent;
        @else color: var(--n-primary, #7C3238); background: transparent; @endif
    ">{{ $label }}</a>
