@php
    $p = $node['props'] ?? [];
    $name = (string) ($p['name'] ?? 'heart');
    $size = (int) ($p['size'] ?? 32);
    $size = max(8, min(200, $size));

    // Small inline set — avoids pulling an icon library onto the public page.
    $paths = [
        'heart' => 'M12 21s-7-4.35-7-10a4 4 0 017-2.65A4 4 0 0119 11c0 5.65-7 10-7 10z',
        'star' => 'M12 3l2.9 6.1 6.6.9-4.8 4.6 1.2 6.6L12 18.1 6.1 21.2l1.2-6.6L2.5 10l6.6-.9L12 3z',
        'location' => 'M12 21s-6-5.3-6-10a6 6 0 1112 0c0 4.7-6 10-6 10z M12 13a2 2 0 100-4 2 2 0 000 4z',
        'calendar' => 'M8 2v4M16 2v4M3 10h18M5 6h14a2 2 0 012 2v11a2 2 0 01-2 2H5a2 2 0 01-2-2V8a2 2 0 012-2z',
        'music' => 'M9 18V6l10-2v12M9 18a2 2 0 11-4 0 2 2 0 014 0zM19 16a2 2 0 11-4 0 2 2 0 014 0z',
        'gift' => 'M20 12v9H4v-9M2 7h20v5H2zM12 21V7M12 7a3 3 0 10-3-3c0 1.7 1.3 3 3 3zM12 7a3 3 0 113-3c0 1.7-1.3 3-3 3z',
        'ring' => 'M12 22a7 7 0 100-14 7 7 0 000 14zM9 8l3-6 3 6',
        'flower' => 'M12 12c0-2 1-3 3-3s3 1 3 3-1 3-3 3c2 0 3 1 3 3s-1 3-3 3-3-1-3-3c0 2-1 3-3 3s-3-1-3-3 1-3 3-3c-2 0-3-1-3-3s1-3 3-3 3 1 3 3z',
    ];

    $path = $paths[$name] ?? $paths['heart'];
@endphp
<svg xmlns="http://www.w3.org/2000/svg" width="{{ $size }}" height="{{ $size }}" viewBox="0 0 24 24"
    fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"
    aria-hidden="true" style="display: block; margin: 0 auto;">
    <path d="{{ $path }}"/>
</svg>
