@php
    $p = $node['props'] ?? [];
    $format = $p['format'] ?? 'long';
@endphp
@if($wedding->date)
<div class="n-inner">
    <p>{{ match ($format) {
        'short' => $wedding->date->translatedFormat('d/m/Y'),
        'medium' => $wedding->date->translatedFormat('d F Y'),
        default => $wedding->date->translatedFormat('l, d F Y'),
    } }}</p>
</div>
@endif
