@php
    $p = $overlay['props'] ?? [];
    $id = preg_replace('/[^A-Za-z0-9_-]/', '', (string) ($overlay['id'] ?? 'ov'));
    $motif = $p['motif'] ?? 'dots';
    $motif = in_array($motif, ['dots', 'grid', 'diagonal'], true) ? $motif : 'dots';
    $size = max(4, min(80, (int) ($p['size'] ?? 16)));
    $color = (string) ($p['color'] ?? '#00000010');
    $color = preg_match('/^#([0-9a-fA-F]{3,8})$/', $color) ? $color : '#00000010';

    $layer = match ($motif) {
        'grid' => "linear-gradient({$color} 1px, transparent 1px), linear-gradient(90deg, {$color} 1px, transparent 1px)",
        'diagonal' => "repeating-linear-gradient(45deg, {$color} 0, {$color} 1px, transparent 1px, transparent {$size}px)",
        default => "radial-gradient({$color} 1.5px, transparent 1.5px)",
    };

    $bgSize = $motif === 'grid' ? "{$size}px {$size}px" : "{$size}px {$size}px";
@endphp
<div data-overlay-id="{{ $id }}" class="n-overlay" aria-hidden="true"
    style="background-image: {{ $layer }}; background-size: {{ $bgSize }};"></div>
