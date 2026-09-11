@php
    $p = $overlay['props'] ?? [];
    $density = max(4, min(60, (int) ($p['density'] ?? 22)));
    $size = max(4, min(60, (int) ($p['size'] ?? 14)));
    $color = $p['color'] ?? '#F5D98B';
    $speed = $p['speed'] ?? 'normal';
    $speed = in_array($speed, ['slow', 'normal', 'fast'], true) ? $speed : 'normal';
    $id = preg_replace('/[^A-Za-z0-9_-]/', '', (string) ($overlay['id'] ?? 'ov'));

    $items = [];
    for ($i = 0; $i < $density; $i++) {
        $items[] = [
            'left' => round(fmod(sin($i * 33.1) * 43758.5453, 1) * 100, 3),
            'top' => round(fmod(sin($i * 17.7) * 12345.678, 1) * 100, 3),
            'delay' => round(fmod(sin($i * 61.3) * 9876.5, 1) * 6, 2),
            'dur' => round(2 + fmod(sin($i * 8.9) * 4567.8, 1) * 3, 2),
        ];
    }
@endphp
<div data-overlay-id="{{ $id }}" class="n-overlay" aria-hidden="true">
    <style>
        [data-overlay-id="{{ $id }}"] .sparkle {
            position: absolute;
            will-change: transform, opacity;
            animation-name: n-twinkle-{{ $id }};
            animation-timing-function: ease-in-out;
            animation-iteration-count: infinite;
        }
        @keyframes n-twinkle-{{ $id }} {
            0%, 100% { opacity: 0; transform: scale(0.4); }
            50%      { opacity: 1; transform: scale(1); }
        }
    </style>
    @foreach($items as $item)
    <span class="sparkle"
        style="left: {{ $item['left'] }}%; top: {{ $item['top'] }}%;
               animation-duration: {{ $item['dur'] * ($speed === 'fast' ? 0.6 : ($speed === 'slow' ? 1.6 : 1)) }}s;
               animation-delay: -{{ $item['delay'] }}s;
               width: {{ $size }}px; height: {{ $size }}px;">
        <svg viewBox="0 0 24 24" fill="{{ $color }}" xmlns="http://www.w3.org/2000/svg">
            <path d="M12 0l2.4 9.6L24 12l-9.6 2.4L12 24l-2.4-9.6L0 12l9.6-2.4z"/>
        </svg>
    </span>
    @endforeach
</div>
