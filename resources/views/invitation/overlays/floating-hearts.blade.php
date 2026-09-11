@php
    $p = $overlay['props'] ?? [];
    $density = max(4, min(60, (int) ($p['density'] ?? 14)));
    $size = max(6, min(80, (int) ($p['size'] ?? 20)));
    $color = $p['color'] ?? '#E8A0A8';
    $speed = $p['speed'] ?? 'normal';
    $speed = in_array($speed, ['slow', 'normal', 'fast'], true) ? $speed : 'normal';
    $id = preg_replace('/[^A-Za-z0-9_-]/', '', (string) ($overlay['id'] ?? 'ov'));

    $items = [];
    for ($i = 0; $i < $density; $i++) {
        $items[] = [
            'left' => round(fmod(sin($i * 21.9898) * 43758.5453, 1) * 100, 3),
            'delay' => round(fmod(sin($i * 45.233) * 12345.678, 1) * 14, 2),
            'dur' => round(10 + fmod(sin($i * 7.17) * 9876.5, 1) * 10, 2),
            'scale' => round(0.5 + fmod(sin($i * 9.71) * 4567.8, 1) * 0.9, 2),
        ];
    }
@endphp
<div data-overlay-id="{{ $id }}" class="n-overlay" aria-hidden="true">
    <style>
        [data-overlay-id="{{ $id }}"] .heart {
            position: absolute;
            bottom: -10%;
            will-change: transform;
            animation-name: n-rise-{{ $id }};
            animation-timing-function: linear;
            animation-iteration-count: infinite;
        }
        @keyframes n-rise-{{ $id }} {
            0%   { transform: translateY(0) translateX(0) scale(0.8); opacity: 0; }
            12%  { opacity: 1; }
            50%  { transform: translateY(-55vh) translateX(14px) scale(1); }
            100% { transform: translateY(-115vh) translateX(-10px) scale(0.9); opacity: 0; }
        }
    </style>
    @foreach($items as $item)
    <span class="heart"
        style="left: {{ $item['left'] }}%;
               animation-duration: {{ $item['dur'] * ($speed === 'fast' ? 0.6 : ($speed === 'slow' ? 1.6 : 1)) }}s;
               animation-delay: -{{ $item['delay'] }}s;
               width: {{ $size }}px; height: {{ $size }}px;
               transform: scale({{ $item['scale'] }});">
        <svg viewBox="0 0 24 24" fill="{{ $color }}" xmlns="http://www.w3.org/2000/svg">
            <path d="M12 21s-7-4.35-7-10a4 4 0 017-2.65A4 4 0 0119 11c0 5.65-7 10-7 10z"/>
        </svg>
    </span>
    @endforeach
</div>
