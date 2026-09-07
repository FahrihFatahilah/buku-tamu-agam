{{-- Single full-width curtain SVG, split via clip-path in CSS --}}
<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 800 900"
    preserveAspectRatio="xMidYMid slice"
    style="width:100%;height:100%;display:block;">

    <defs>
        {{-- Gradient kain: gelap di tepi, terang di tengah --}}
        <linearGradient id="fabric-full" x1="0" y1="0" x2="1" y2="0">
            <stop offset="0%"   stop-color="#2a0a0d"/>
            <stop offset="8%"   stop-color="#4a1a1e"/>
            <stop offset="20%"  stop-color="#7C3238"/>
            <stop offset="35%"  stop-color="#8f3a40"/>
            <stop offset="50%"  stop-color="#9a3e44"/>
            <stop offset="65%"  stop-color="#8f3a40"/>
            <stop offset="80%"  stop-color="#7C3238"/>
            <stop offset="92%"  stop-color="#4a1a1e"/>
            <stop offset="100%" stop-color="#2a0a0d"/>
        </linearGradient>

        {{-- Gradient lipatan --}}
        <linearGradient id="foldA" x1="0" y1="0" x2="1" y2="0">
            <stop offset="0%"   stop-color="rgba(0,0,0,0.4)"/>
            <stop offset="45%"  stop-color="rgba(255,255,255,0.08)"/>
            <stop offset="100%" stop-color="rgba(0,0,0,0.3)"/>
        </linearGradient>
        <linearGradient id="foldB" x1="0" y1="0" x2="1" y2="0">
            <stop offset="0%"   stop-color="rgba(0,0,0,0.25)"/>
            <stop offset="50%"  stop-color="rgba(255,255,255,0.05)"/>
            <stop offset="100%" stop-color="rgba(0,0,0,0.2)"/>
        </linearGradient>

        {{-- Pattern songket --}}
        <pattern id="songket" x="0" y="0" width="20" height="20" patternUnits="userSpaceOnUse">
            <line x1="0" y1="20" x2="20" y2="0" stroke="#B8960C" stroke-width="0.5" opacity="0.18"/>
            <line x1="-5" y1="20" x2="15" y2="0" stroke="#B8960C" stroke-width="0.2" opacity="0.08"/>
        </pattern>

        {{-- Pattern tenun --}}
        <pattern id="weave" x="0" y="0" width="4" height="4" patternUnits="userSpaceOnUse">
            <rect x="0" y="0" width="4" height="1" fill="rgba(0,0,0,0.07)"/>
            <rect x="0" y="2" width="4" height="1" fill="rgba(255,255,255,0.025)"/>
        </pattern>
    </defs>

    {{-- Base kain --}}
    <rect width="800" height="900" fill="url(#fabric-full)"/>
    <rect width="800" height="900" fill="url(#weave)"/>
    <rect width="800" height="900" fill="url(#songket)"/>

    {{-- Lipatan kain — simetris kiri & kanan --}}
    {{-- Kiri --}}
    <rect x="55"  y="0" width="26" height="900" fill="url(#foldA)" opacity="0.7"/>
    <rect x="130" y="0" width="18" height="900" fill="url(#foldB)" opacity="0.6"/>
    <rect x="210" y="0" width="22" height="900" fill="url(#foldA)" opacity="0.5"/>
    <rect x="300" y="0" width="14" height="900" fill="url(#foldB)" opacity="0.4"/>
    {{-- Kanan (mirror) --}}
    <rect x="745" y="0" width="26" height="900" fill="url(#foldA)" opacity="0.7"/>
    <rect x="652" y="0" width="18" height="900" fill="url(#foldB)" opacity="0.6"/>
    <rect x="568" y="0" width="22" height="900" fill="url(#foldA)" opacity="0.5"/>
    <rect x="486" y="0" width="14" height="900" fill="url(#foldB)" opacity="0.4"/>

    {{-- Highlight lipatan --}}
    <rect x="53"  y="0" width="2" height="900" fill="rgba(255,255,255,0.07)"/>
    <rect x="128" y="0" width="2" height="900" fill="rgba(255,255,255,0.05)"/>
    <rect x="208" y="0" width="2" height="900" fill="rgba(255,255,255,0.04)"/>
    <rect x="745" y="0" width="2" height="900" fill="rgba(255,255,255,0.07)"/>
    <rect x="650" y="0" width="2" height="900" fill="rgba(255,255,255,0.05)"/>
    <rect x="566" y="0" width="2" height="900" fill="rgba(255,255,255,0.04)"/>

    {{-- Border atas & bawah --}}
    <rect x="0" y="0"   width="800" height="3" fill="#B8960C" opacity="0.5"/>
    <rect x="0" y="5"   width="800" height="1" fill="#B8960C" opacity="0.2"/>
    <rect x="0" y="897" width="800" height="3" fill="#B8960C" opacity="0.5"/>
    <rect x="0" y="893" width="800" height="1" fill="#B8960C" opacity="0.2"/>

    {{-- Ornamen Rumah Gadang kiri --}}
    <image href="{{ asset('images/rumah-gadang.svg') }}"
        x="0" y="500" width="800" height="400" opacity="0.88"
        preserveAspectRatio="xMidYMax meet"/>


    {{-- Ornamen Rumah Gadang kanan --}}
    <g transform="translate(600, 90)" opacity="0.18">
        <path d="M-70,35 L-52,8 L-35,22 L-35,8 L-18,0 L0,8 L18,0 L35,8 L35,22 L52,8 L70,35 Z" fill="#B8960C"/>
        <rect x="-13" y="22" width="26" height="13" fill="#B8960C"/>
        <rect x="-32" y="25" width="13" height="10" fill="#B8960C"/>
        <rect x="19"  y="25" width="13" height="10" fill="#B8960C"/>
    </g>

    {{-- Ornamen diamond kiri --}}
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

    {{-- Ornamen diamond kanan --}}
    <g transform="translate(600, 450)" opacity="0.14">
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

</svg>
