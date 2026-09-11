@php
    $p = $overlay['props'] ?? [];
    $density = max(4, min(60, (int) ($p['density'] ?? 16)));
    $size = max(4, min(40, (int) ($p['size'] ?? 10)));
    $color = $p['color'] ?? '#FFE9A8';
    $speed = $p['speed'] ?? 'slow';
    $speed = in_array($speed, ['slow', 'normal', 'fast'], true) ? $speed : 'slow';
    $id = preg_replace('/[^A-Za-z0-9_-]/', '', (string) ($overlay['id'] ?? 'ov'));

    $items = [];
    for ($i = 0; $i < $density; $i++) {
        $items[] = [
            'left' => round(fmod(sin($i * 11.3) * 43758.5, 1) * 100, 3),
            'top' => round(20 + fmod(sin($i * 29.7) * 12345.6, 1) * 75, 3),
            'delay' => round(fmod(sin($i * 53.9) * 9876.5, 1) * 10, 2),
            'dur' => round(4 + fmod(sin($i * 4.4) * 4567.8, 1) * 6, 2),
        ];
    }
@endphp
<div data-overlay-id="{{ $id }}" class="n-overlay" aria-hidden="true">
    <style>
        [data-overlay-id="{{ $id }}"] .firefly {
            position: absolute;
            border-radius: 9999px;
            background: {{ $color }};
            box-shadow: 0 0 8px 2px {{ $color }};
            will-change: transform, opacity;
            animation-name: n-drift-{{ $id }};
            animation-timing-function: ease-in-out;
            animation-iteration-count: infinite;
        }
        @keyframes n-drift-{{ $id }} {
            0%, 100% { opacity: 0.15; transform: translate(0, 0) scale(0.8); }
            25%      { opacity: 0.9; transform: translate(18px, -22px) scale(1.1); }
            50%      { opacity: 0.35; transform: translate(-14px, -40px) scale(0.9); }
            75%      { opacity: 0.85; transform: translate(12px, -18px) scale(1.05); }
        }
    </style>
    @foreach($items as $item)
    <span class="firefly"
        style="left: {{ $item['left'] }}%; top: {{ $item['top'] }}%;
               width: {{ $size }}px; height: {{ $size }}px;
               animation-duration: {{ $item['dur'] * ($speed === 'fast' ? 0.6 : ($speed === 'slow' ? 1.6 : 1)) }}s;
               animation-delay: -{{ $item['delay'] }}s;"></span>
    @endforeach
</div>
