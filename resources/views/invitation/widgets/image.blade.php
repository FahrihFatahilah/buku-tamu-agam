@php
    $p = $node['props'] ?? [];
    $src = is_string($p['src'] ?? null) ? $p['src'] : '';
    $alt = (string) ($p['alt'] ?? '');
    $fit = $p['objectFit'] ?? 'cover';
    $fit = in_array($fit, ['cover', 'contain', 'fill', 'none', 'scale-down'], true) ? $fit : 'cover';

    // Only storage-relative paths are accepted — no schemes, no traversal.
    $valid = $src !== ''
        && preg_match('#^[A-Za-z0-9][A-Za-z0-9/_.-]*\.(jpg|jpeg|png|webp|gif|svg|avif)$#i', $src)
        && ! str_contains($src, '..');

    $url = $valid
        ? (str_starts_with($src, 'builtin:') ? asset('builder/decorations/' . substr($src, 8) . '.svg') : Storage::url($src))
        : null;
@endphp
@if($url)
<img src="{{ $url }}" alt="{{ $alt !== '' ? $alt : $wedding->coupleName() }}"
    loading="lazy" decoding="async" class="w-full h-auto block" style="object-fit: {{ $fit }};">
@endif
