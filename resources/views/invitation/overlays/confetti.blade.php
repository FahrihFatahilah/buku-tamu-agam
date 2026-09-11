@php
    $p = $overlay['props'] ?? [];
    $density = max(4, min(60, (int) ($p['density'] ?? 24)));
    $size = max(4, min(40, (int) ($p['size'] ?? 12)));
    $color = $p['color'] ?? '#C9A84C';
    $speed = $p['speed'] ?? 'fast';
    $speed = in_array($speed, ['slow', 'normal', 'fast'], true) ? $speed : 'fast';
    $id = preg_replace('/[^A-Za-z0-9_-]/', '', (string) ($overlay['id'] ?? 'ov'));

    $items = [];
    for ($i = 0; $i < $density; $i++) {
        $items[] = [
            'left' => round(fmod(sin($i * 19.1) * 43758.5, 1) * 100, 3),
            'delay' => round(fmod(sin($i * 37.4) * 12345.6, 1) * 8, 2),
            'dur' => round(3 + fmod(sin($i * 6.3) * 9876.5, 1) * 4, 2),
            'hue' => (int) (fmod(sin($i * 2.9) * 4567.8, 1) * 360),
        ];
    }
@endphp
<div data-overlay-id="{{ $id }}" class="n-overlay" aria-hidden="true">
    <style>
        [data-overlay-id="{{ $id }}"] .confetti {
            position: absolute;
            top: -8%;
            will-change: transform;
            animation-name: n-confetti-{{ $id }};
            animation-timing-function: linear;
            animation-iteration-count: infinite;
        }
        @keyframes n-confetti-{{ $id }} {
            0%   { transform: translateY(-10vh) rotate(0deg); opacity: 0; }
            8%   { opacity: 1; }
            100% { transform: translateY(112vh) rotate(720deg); opacity: 0; }
        }
    </style>
    @foreach($items as $item)
    <span class="confetti"
        style="left: {{ $item['left'] }}%;
               width: {{ $size }}px; height: {{ max(4, (int) ($size * 0.4)) }}px;
               background: {{ $color }};
               filter: hue-rotate({{ $item['hue'] }}deg);
               animation-duration: {{ $item['dur'] * ($speed === 'fast' ? 0.6 : ($speed === 'slow' ? 1.6 : 1)) }}s;
               animation-delay: -{{ $item['delay'] }}s;"></span>
    @endforeach
</div>
