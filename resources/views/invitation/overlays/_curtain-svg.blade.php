@php
$side    = $side    ?? 'left';
$primary = $primary ?? '#7C3238';
$accent  = $accent  ?? '#B8960C';
$ornament= $ornament?? 'minang';
$isLeft  = $side === 'left';
$isFull  = $side === 'full';
$uid     = $side . '-' . substr(md5($primary.$accent), 0, 6);
@endphp

<svg xmlns="http://www.w3.org/2000/svg"
    viewBox="0 0 {{ $isFull ? 800 : 400 }} 900"
    preserveAspectRatio="xMidYMid slice"
    style="width:100%;height:100%;display:block;position:absolute;inset:0;">
<defs>
    <linearGradient id="fab-{{ $uid }}"
        x1="{{ ($isFull || $isLeft) ? '0' : '1' }}" y1="0"
        x2="{{ ($isFull || $isLeft) ? '1' : '0' }}" y2="0">
        <stop offset="0%"   stop-color="{{ $primary }}" stop-opacity=".55"/>
        <stop offset="25%"  stop-color="{{ $primary }}" stop-opacity=".85"/>
        <stop offset="55%"  stop-color="{{ $primary }}"/>
        <stop offset="80%"  stop-color="{{ $primary }}" stop-opacity=".85"/>
        <stop offset="100%" stop-color="{{ $primary }}" stop-opacity=".5"/>
    </linearGradient>

    <linearGradient id="fold1-{{ $uid }}" x1="0" y1="0" x2="1" y2="0">
        <stop offset="0%"   stop-color="rgba(0,0,0,.35)"/>
        <stop offset="40%"  stop-color="rgba(255,255,255,.07)"/>
        <stop offset="100%" stop-color="rgba(0,0,0,.25)"/>
    </linearGradient>
    <linearGradient id="fold2-{{ $uid }}" x1="0" y1="0" x2="1" y2="0">
        <stop offset="0%"   stop-color="rgba(0,0,0,.2)"/>
        <stop offset="50%"  stop-color="rgba(255,255,255,.05)"/>
        <stop offset="100%" stop-color="rgba(0,0,0,.18)"/>
    </linearGradient>

    <pattern id="songket-{{ $uid }}" x="0" y="0" width="20" height="20" patternUnits="userSpaceOnUse">
        <line x1="0" y1="20" x2="20" y2="0" stroke="{{ $accent }}" stroke-width=".5" opacity=".2"/>
        <line x1="-5" y1="20" x2="15" y2="0" stroke="{{ $accent }}" stroke-width=".2" opacity=".1"/>
    </pattern>
    <pattern id="weave-{{ $uid }}" x="0" y="0" width="4" height="4" patternUnits="userSpaceOnUse">
        <rect x="0" y="0" width="4" height="1" fill="rgba(0,0,0,.07)"/>
        <rect x="0" y="2" width="4" height="1" fill="rgba(255,255,255,.025)"/>
    </pattern>
</defs>

@php $w = $isFull ? 800 : 400; @endphp
<rect width="{{ $w }}" height="900" fill="url(#fab-{{ $uid }})"/>
<rect width="{{ $w }}" height="900" fill="url(#weave-{{ $uid }})"/>
<rect width="{{ $w }}" height="900" fill="url(#songket-{{ $uid }})"/>

{{-- Fold lines --}}
@if($isLeft || $isFull)
<rect x="55"  y="0" width="26" height="900" fill="url(#fold1-{{ $uid }})" opacity=".7"/>
<rect x="130" y="0" width="18" height="900" fill="url(#fold2-{{ $uid }})" opacity=".6"/>
<rect x="210" y="0" width="22" height="900" fill="url(#fold1-{{ $uid }})" opacity=".5"/>
<rect x="300" y="0" width="14" height="900" fill="url(#fold2-{{ $uid }})" opacity=".4"/>
<rect x="53"  y="0" width="2"  height="900" fill="rgba(255,255,255,.07)"/>
<rect x="128" y="0" width="2"  height="900" fill="rgba(255,255,255,.05)"/>
<rect x="208" y="0" width="2"  height="900" fill="rgba(255,255,255,.04)"/>
@endif
@if(!$isLeft || $isFull)
@php $ox = $isFull ? 400 : 0; @endphp
<rect x="{{ $ox+68 }}"  y="0" width="14" height="900" fill="url(#fold2-{{ $uid }})" opacity=".4"/>
<rect x="{{ $ox+158 }}" y="0" width="22" height="900" fill="url(#fold1-{{ $uid }})" opacity=".5"/>
<rect x="{{ $ox+247 }}" y="0" width="18" height="900" fill="url(#fold2-{{ $uid }})" opacity=".6"/>
<rect x="{{ $ox+317 }}" y="0" width="28" height="900" fill="url(#fold1-{{ $uid }})" opacity=".7"/>
@endif

{{-- Gold border top/bottom --}}
<rect x="0" y="0"   width="{{ $w }}" height="3" fill="{{ $accent }}" opacity=".5"/>
<rect x="0" y="5"   width="{{ $w }}" height="1" fill="{{ $accent }}" opacity=".2"/>
<rect x="0" y="897" width="{{ $w }}" height="3" fill="{{ $accent }}" opacity=".5"/>
<rect x="0" y="893" width="{{ $w }}" height="1" fill="{{ $accent }}" opacity=".2"/>

{{-- Ornament --}}
@if($ornament === 'minang')
<image href="{{ asset('images/rumah-gadang.svg') }}"
    x="{{ $isFull ? 0 : ($isLeft ? -100 : -100) }}" y="500"
    width="{{ $isFull ? 800 : 600 }}" height="400"
    opacity=".85" preserveAspectRatio="xMidYMax meet"/>
<g transform="translate({{ $isFull ? 200 : 200 }}, 420)" opacity=".13">
    <polygon points="0,-36 26,0 0,36 -26,0" fill="none" stroke="{{ $accent }}" stroke-width="1.5"/>
    <polygon points="0,-18 13,0 0,18 -13,0" fill="none" stroke="{{ $accent }}" stroke-width="1"/>
    <circle cx="0"   cy="-36" r="2.5" fill="{{ $accent }}"/>
    <circle cx="26"  cy="0"   r="2.5" fill="{{ $accent }}"/>
    <circle cx="0"   cy="36"  r="2.5" fill="{{ $accent }}"/>
    <circle cx="-26" cy="0"   r="2.5" fill="{{ $accent }}"/>
</g>

@elseif($ornament === 'floral')
<g transform="translate({{ $isFull ? 400 : 200 }}, 450)" opacity=".18">
    @for($i=0;$i<8;$i++)
    <ellipse cx="0" cy="-45" rx="12" ry="22" fill="{{ $accent }}" opacity=".6"
        transform="rotate({{ $i*45 }})"/>
    @endfor
    <circle cx="0" cy="0" r="14" fill="{{ $accent }}" opacity=".8"/>
</g>
@endif
</svg>
