@php
    $p = $overlay['props'] ?? [];
    $density = max(4, min(60, (int) ($p['density'] ?? 18)));
    $size = max(6, min(80, (int) ($p['size'] ?? 18)));
    $color = $p['color'] ?? '#E8B4B8';
    $speed = $p['speed'] ?? 'normal';
    $speed = in_array($speed, ['slow', 'normal', 'fast'], true) ? $speed : 'normal';
    $id = preg_replace('/[^A-Za-z0-9_-]/', '', (string) ($overlay['id'] ?? 'ov'));

    // Deterministic pseudo-random layout: no JS needed for placement.
    $items = [];
    for ($i = 0; $i < $density; $i++) {
        $items[] = [
            'left' => round(fmod(sin($i * 12.9898) * 43758.5453, 1) * 100, 3),
            'delay' => round(fmod(sin($i * 78.233) * 12345.678, 1) * 12, 2),
            'dur' => round(8 + fmod(sin($i * 3.17) * 9876.5, 1) * 8, 2),
            'scale' => round(0.6 + fmod(sin($i * 5.71) * 4567.8, 1) * 0.8, 2),
        ];
    }
@endphp
<div data-overlay-id="{{ $id }}" class="n-overlay" aria-hidden="true" style="overflow: hidden;">
    <style>
        [data-overlay-id="{{ $id }}"] .petal {
            position: absolute;
            top: -10%;
            will-change: transform;
            animation-name: n-fall-{{ $id }};
            animation-timing-function: linear;
            animation-iteration-count: infinite;
        }
        @keyframes n-fall-{{ $id }} {
            0%   { transform: translateY(-10vh) rotate(0deg); opacity: 0; }
            10%  { opacity: 1; }
            100% { transform: translateY(110vh) rotate(360deg); opacity: 0; }
        }
    </style>
    @foreach($items as $item)
    <span class="petal"
        style="left: {{ $item['left'] }}%;
               animation-duration: {{ $item['dur'] * ($speed === 'fast' ? 0.6 : ($speed === 'slow' ? 1.6 : 1)) }}s;
               animation-delay: -{{ $item['delay'] }}s;
               width: {{ $size }}px; height: {{ $size }}px;
               transform: scale({{ $item['scale'] }});">
        <svg viewBox="0 0 24 24" fill="{{ $color }}" xmlns="http://www.w3.org/2000/svg">
            <path d="M12 2c5 4 8 7 8 11a8 8 0 11-16 0c0-4 3-7 8-11z"/>
        </svg>
    </span>
    @endforeach
</div>
