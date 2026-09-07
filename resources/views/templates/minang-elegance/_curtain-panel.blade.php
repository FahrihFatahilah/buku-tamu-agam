{{--
    Curtain panel SVG — kain tirai dengan tekstur lipatan dan ornamen Minang
    Usage: @include('templates.minang-elegance._curtain-panel', ['side' => 'left'|'right'])
--}}
@php $isLeft = ($side ?? 'left') === 'left'; @endphp

<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 400 900"
    preserveAspectRatio="xMidYMid slice"
    style="width:100%;height:100%;display:block;">

    <defs>
        {{-- Gradient utama kain --}}
        <linearGradient id="fabric-{{ $side }}" x1="{{ $isLeft ? '0' : '1' }}" y1="0" x2="{{ $isLeft ? '1' : '0' }}" y2="0">
            <stop offset="0%"   stop-color="#3d0f12"/>
            <stop offset="25%"  stop-color="#6a2a2f"/>
            <stop offset="55%"  stop-color="#8f3a40"/>
            <stop offset="75%"  stop-color="#7C3238"/>
            {{-- Tepi dalam (tengah layar) gelap — seamless dengan panel lain --}}
            <stop offset="95%"  stop-color="#4a1a1e"/>
            <stop offset="100%" stop-color="#2a0a0d"/>
        </linearGradient>

        {{-- Gradient lipatan kain --}}
        <linearGradient id="fold1-{{ $side }}" x1="0" y1="0" x2="1" y2="0">
            <stop offset="0%"   stop-color="rgba(0,0,0,0.35)"/>
            <stop offset="40%"  stop-color="rgba(255,255,255,0.07)"/>
            <stop offset="100%" stop-color="rgba(0,0,0,0.25)"/>
        </linearGradient>
        <linearGradient id="fold2-{{ $side }}" x1="0" y1="0" x2="1" y2="0">
            <stop offset="0%"   stop-color="rgba(0,0,0,0.2)"/>
            <stop offset="50%"  stop-color="rgba(255,255,255,0.05)"/>
            <stop offset="100%" stop-color="rgba(0,0,0,0.18)"/>
        </linearGradient>

        {{-- Shadow tepi luar (kiri/kanan layar) --}}
        <linearGradient id="outer-shadow-{{ $side }}" x1="{{ $isLeft ? '0' : '1' }}" y1="0" x2="{{ $isLeft ? '1' : '0' }}" y2="0">
            <stop offset="0%"   stop-color="rgba(0,0,0,0.55)"/>
            <stop offset="20%"  stop-color="rgba(0,0,0,0)"/>
        </linearGradient>

        {{-- Shadow tepi dalam (tengah) — gelap agar seamless --}}
        <linearGradient id="inner-shadow-{{ $side }}" x1="{{ $isLeft ? '1' : '0' }}" y1="0" x2="{{ $isLeft ? '0' : '1' }}" y2="0">
            <stop offset="0%"   stop-color="rgba(0,0,0,0.6)"/>
            <stop offset="15%"  stop-color="rgba(0,0,0,0)"/>
        </linearGradient>

        {{-- Pattern songket diagonal emas --}}
        <pattern id="songket-{{ $side }}" x="0" y="0" width="20" height="20" patternUnits="userSpaceOnUse">
            <line x1="0" y1="20" x2="20" y2="0" stroke="#B8960C" stroke-width="0.5" opacity="0.2"/>
            <line x1="-5" y1="20" x2="15" y2="0" stroke="#B8960C" stroke-width="0.2" opacity="0.1"/>
            <line x1="5" y1="20" x2="25" y2="0" stroke="#B8960C" stroke-width="0.2" opacity="0.1"/>
        </pattern>

        {{-- Pattern tenun horizontal --}}
        <pattern id="weave-{{ $side }}" x="0" y="0" width="4" height="4" patternUnits="userSpaceOnUse">
            <rect x="0" y="0" width="4" height="1" fill="rgba(0,0,0,0.07)"/>
            <rect x="0" y="2" width="4" height="1" fill="rgba(255,255,255,0.025)"/>
        </pattern>
    </defs>

    {{-- Base kain --}}
    <rect width="400" height="900" fill="url(#fabric-{{ $side }})"/>

    {{-- Tekstur tenun --}}
    <rect width="400" height="900" fill="url(#weave-{{ $side }})"/>

    {{-- Motif songket --}}
    <rect width="400" height="900" fill="url(#songket-{{ $side }})"/>

    {{-- Lipatan kain vertikal --}}
    @if($isLeft)
    <rect x="55"  y="0" width="28" height="900" fill="url(#fold1-{{ $side }})" opacity="0.7"/>
    <rect x="135" y="0" width="18" height="900" fill="url(#fold2-{{ $side }})" opacity="0.6"/>
    <rect x="220" y="0" width="22" height="900" fill="url(#fold1-{{ $side }})" opacity="0.5"/>
    <rect x="310" y="0" width="14" height="900" fill="url(#fold2-{{ $side }})" opacity="0.4"/>
    {{-- Highlight lipatan --}}
    <rect x="53"  y="0" width="2" height="900" fill="rgba(255,255,255,0.07)"/>
    <rect x="133" y="0" width="2" height="900" fill="rgba(255,255,255,0.05)"/>
    <rect x="218" y="0" width="2" height="900" fill="rgba(255,255,255,0.04)"/>
    @else
    <rect x="68"  y="0" width="14" height="900" fill="url(#fold2-{{ $side }})" opacity="0.4"/>
    <rect x="158" y="0" width="22" height="900" fill="url(#fold1-{{ $side }})" opacity="0.5"/>
    <rect x="247" y="0" width="18" height="900" fill="url(#fold2-{{ $side }})" opacity="0.6"/>
    <rect x="317" y="0" width="28" height="900" fill="url(#fold1-{{ $side }})" opacity="0.7"/>
    {{-- Highlight lipatan --}}
    <rect x="66"  y="0" width="2" height="900" fill="rgba(255,255,255,0.04)"/>
    <rect x="156" y="0" width="2" height="900" fill="rgba(255,255,255,0.05)"/>
    <rect x="245" y="0" width="2" height="900" fill="rgba(255,255,255,0.07)"/>
    @endif

    {{-- Shadow tepi luar --}}
    <rect width="400" height="900" fill="url(#outer-shadow-{{ $side }})"/>

    {{-- Shadow tepi dalam — gelap seamless, TANPA garis emas --}}
    <rect width="400" height="900" fill="url(#inner-shadow-{{ $side }})"/>

    {{-- Border atas --}}
    <rect x="0" y="0" width="400" height="3" fill="#B8960C" opacity="0.5"/>
    <rect x="0" y="5" width="400" height="1" fill="#B8960C" opacity="0.2"/>

    {{-- Border bawah --}}
    <rect x="0" y="897" width="400" height="3" fill="#B8960C" opacity="0.5"/>
    <rect x="0" y="893" width="400" height="1" fill="#B8960C" opacity="0.2"/>

    {{-- Ornamen Rumah Gadang atas --}}
    <g transform="translate(200, 90)" opacity="0.18">
        <path d="M-70,35 L-52,8 L-35,22 L-35,8 L-18,0 L0,8 L18,0 L35,8 L35,22 L52,8 L70,35 Z" fill="#B8960C"/>
        <rect x="-13" y="22" width="26" height="13" fill="#B8960C"/>
        <rect x="-32" y="25" width="13" height="10" fill="#B8960C"/>
        <rect x="19"  y="25" width="13" height="10" fill="#B8960C"/>
    </g>

    {{-- Ornamen geometris tengah --}}
    <g transform="translate(200, 450)" opacity="0.14">
        <polygon points="0,-38 28,0 0,38 -28,0" fill="none" stroke="#B8960C" stroke-width="1.5"/>
        <polygon points="0,-19 14,0 0,19 -14,0" fill="none" stroke="#B8960C" stroke-width="1"/>
        <circle cx="0"   cy="-38" r="2.5" fill="#B8960C"/>
        <circle cx="28"  cy="0"   r="2.5" fill="#B8960C"/>
        <circle cx="0"   cy="38"  r="2.5" fill="#B8960C"/>
        <circle cx="-28" cy="0"   r="2.5" fill="#B8960C"/>
        <line x1="-45" y1="0" x2="-32" y2="0" stroke="#B8960C" stroke-width="0.8"/>
        <line x1="32"  y1="0" x2="45"  y2="0" stroke="#B8960C" stroke-width="0.8"/>
        <line x1="0" y1="-52" x2="0" y2="-42" stroke="#B8960C" stroke-width="0.8"/>
        <line x1="0" y1="42"  x2="0" y2="52"  stroke="#B8960C" stroke-width="0.8"/>
    </g>

    {{-- Ornamen geometris bawah --}}
    <g transform="translate(200, 800)" opacity="0.12">
        <polygon points="0,-22 18,0 0,22 -18,0" fill="none" stroke="#B8960C" stroke-width="1.2"/>
        <polygon points="0,-11 9,0 0,11 -9,0"   fill="none" stroke="#B8960C" stroke-width="0.8"/>
        <circle cx="0"   cy="-22" r="2" fill="#B8960C"/>
        <circle cx="18"  cy="0"   r="2" fill="#B8960C"/>
        <circle cx="0"   cy="22"  r="2" fill="#B8960C"/>
        <circle cx="-18" cy="0"   r="2" fill="#B8960C"/>
    </g>

</svg>
