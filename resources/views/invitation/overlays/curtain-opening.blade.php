@php
$p       = $overlay['props'] ?? [];
$style   = $p['style']        ?? 'panel';
$ornament= $p['ornament']     ?? 'minang';
$primary = $p['primaryColor'] ?? '#7C3238';
$accent  = $p['accentColor']  ?? '#B8960C';
$cta     = $p['cta']          ?? 'Buka Undangan';
$showGuest = ($p['showGuest'] ?? true) !== false;
$withClosing = ($p['closing'] ?? true) !== false;

// Sanitise colours — only hex allowed in inline styles
$safePrimary = preg_match('/^#[0-9a-fA-F]{3,8}$/', $primary) ? $primary : '#7C3238';
$safeAccent  = preg_match('/^#[0-9a-fA-F]{3,8}$/', $accent)  ? $accent  : '#B8960C';

// Derive a darker shade for gradients
$isEditor = $isEditor ?? false;
@endphp

{{-- ── Opening curtain ──────────────────────────────────────────────────── --}}
<div id="ngd-curtain-open"
    data-curtain-style="{{ $style }}"
    style="position:fixed;inset:0;z-index:9999;overflow:hidden;background:{{ $safePrimary }};">

    @if($style === 'panel')
    {{-- Left panel --}}
    <div id="ngd-cl" style="position:absolute;top:0;bottom:0;left:0;width:52%;overflow:hidden;transition:transform .98s cubic-bezier(.77,0,.18,1);">
        @include('invitation.overlays._curtain-svg', ['side'=>'left','primary'=>$safePrimary,'accent'=>$safeAccent,'ornament'=>$ornament])
        <div class="ngd-strips" style="position:absolute;inset:0;display:flex;">
            @for($i=0;$i<6;$i++)<div class="ngd-strip" style="flex:1;height:100%;background:repeating-linear-gradient(to right,rgba(0,0,0,0) 0%,rgba(0,0,0,.22) 28%,rgba(255,255,255,.07) 50%,rgba(0,0,0,.28) 72%,rgba(0,0,0,0) 100%);animation:ngd-swing 2.8s ease-in-out infinite;animation-delay:{{ $i * -0.5 }}s;"></div>@endfor
        </div>
    </div>
    {{-- Right panel --}}
    <div id="ngd-cr" style="position:absolute;top:0;bottom:0;right:0;width:52%;overflow:hidden;transition:transform .98s cubic-bezier(.77,0,.18,1);">
        @include('invitation.overlays._curtain-svg', ['side'=>'right','primary'=>$safePrimary,'accent'=>$safeAccent,'ornament'=>$ornament])
        <div class="ngd-strips" style="position:absolute;inset:0;display:flex;">
            @for($i=0;$i<6;$i++)<div class="ngd-strip" style="flex:1;height:100%;background:repeating-linear-gradient(to right,rgba(0,0,0,0) 0%,rgba(0,0,0,.22) 28%,rgba(255,255,255,.07) 50%,rgba(0,0,0,.28) 72%,rgba(0,0,0,0) 100%);animation:ngd-swing 2.8s ease-in-out infinite;animation-delay:{{ $i * -0.5 }}s;"></div>@endfor
        </div>
    </div>

    @elseif($style === 'top')
    <div id="ngd-ct" style="position:absolute;inset:0;transition:transform .98s cubic-bezier(.77,0,.18,1);">
        @include('invitation.overlays._curtain-svg', ['side'=>'full','primary'=>$safePrimary,'accent'=>$safeAccent,'ornament'=>$ornament])
    </div>

    @elseif($style === 'bottom')
    <div id="ngd-cb" style="position:absolute;inset:0;transition:transform .98s cubic-bezier(.77,0,.18,1);">
        @include('invitation.overlays._curtain-svg', ['side'=>'full','primary'=>$safePrimary,'accent'=>$safeAccent,'ornament'=>$ornament])
    </div>

    @else {{-- full --}}
    <div id="ngd-cf" style="position:absolute;inset:0;transition:opacity .98s ease;">
        @include('invitation.overlays._curtain-svg', ['side'=>'full','primary'=>$safePrimary,'accent'=>$safeAccent,'ornament'=>$ornament])
    </div>
    @endif

    {{-- Gradient veil --}}
    <div style="position:absolute;inset:0;background:linear-gradient(to top,rgba(0,0,0,0),rgba(0,0,0,.45));pointer-events:none;z-index:1;transition:opacity .6s ease;" id="ngd-veil"></div>

    {{-- UI --}}
    <div id="ngd-ui" style="position:absolute;inset:0;display:flex;flex-direction:column;align-items:center;justify-content:center;text-align:center;padding:2rem;z-index:2;transition:opacity .3s ease,transform .3s ease;">
        <p style="font-family:'Playfair Display',serif;color:rgba(255,255,255,.65);font-style:italic;font-size:.8rem;letter-spacing:.1em;margin-bottom:.5rem;">Kepada Yth.</p>

        @if($showGuest && $guest)
        <p style="font-family:'Playfair Display',serif;color:#fff;font-size:1.15rem;margin-bottom:1rem;">{{ $guest->name }}</p>
        @endif

        <div style="width:2rem;height:1px;background:{{ $safeAccent }};opacity:.6;margin:0 auto .75rem;"></div>

        <p style="font-family:'Playfair Display',serif;color:#fff;font-size:1.7rem;line-height:1.2;margin-bottom:.15rem;">{{ $wedding->bride_name }}</p>
        @if($wedding->bride_nickname)
        <p style="color:rgba(255,255,255,.45);font-size:.8rem;letter-spacing:.08em;margin-bottom:.4rem;">{{ $wedding->bride_nickname }}</p>
        @endif
        <p style="color:{{ $safeAccent }};font-size:1.1rem;margin-bottom:.4rem;">&amp;</p>
        <p style="font-family:'Playfair Display',serif;color:#fff;font-size:1.7rem;line-height:1.2;margin-bottom:.15rem;">{{ $wedding->groom_name }}</p>
        @if($wedding->groom_nickname)
        <p style="color:rgba(255,255,255,.45);font-size:.8rem;letter-spacing:.08em;margin-bottom:2rem;">{{ $wedding->groom_nickname }}</p>
        @else
        <div style="margin-bottom:2rem;"></div>
        @endif

        <button id="ngd-btn-open"
            style="display:inline-flex;align-items:center;gap:.5rem;padding:.7rem 1.75rem;border:1px solid {{ $safeAccent }};color:{{ $safeAccent }};font-size:.8rem;letter-spacing:.18em;text-transform:uppercase;background:transparent;cursor:pointer;transition:background .2s;">
            {{ $cta }}
        </button>
    </div>
</div>

{{-- ── Closing curtain ──────────────────────────────────────────────────── --}}
@if($withClosing && !$isEditor)
<div id="ngd-curtain-close"
    data-curtain-style="{{ $style }}"
    style="position:fixed;inset:0;z-index:9998;pointer-events:none;display:none;overflow:hidden;">

    @if($style === 'panel')
    <div id="ngd-ccl" style="position:absolute;top:0;bottom:0;left:0;width:52%;overflow:hidden;transform:translateX(-100%);transition:transform .98s cubic-bezier(.77,0,.18,1);">
        @include('invitation.overlays._curtain-svg', ['side'=>'left','primary'=>$safePrimary,'accent'=>$safeAccent,'ornament'=>$ornament])
    </div>
    <div id="ngd-ccr" style="position:absolute;top:0;bottom:0;right:0;width:52%;overflow:hidden;transform:translateX(100%);transition:transform .98s cubic-bezier(.77,0,.18,1);">
        @include('invitation.overlays._curtain-svg', ['side'=>'right','primary'=>$safePrimary,'accent'=>$safeAccent,'ornament'=>$ornament])
    </div>

    @elseif($style === 'top')
    <div id="ngd-cct" style="position:absolute;inset:0;transform:translateY(-100%);transition:transform .98s cubic-bezier(.77,0,.18,1);">
        @include('invitation.overlays._curtain-svg', ['side'=>'full','primary'=>$safePrimary,'accent'=>$safeAccent,'ornament'=>$ornament])
    </div>

    @elseif($style === 'bottom')
    <div id="ngd-ccb" style="position:absolute;inset:0;transform:translateY(100%);transition:transform .98s cubic-bezier(.77,0,.18,1);">
        @include('invitation.overlays._curtain-svg', ['side'=>'full','primary'=>$safePrimary,'accent'=>$safeAccent,'ornament'=>$ornament])
    </div>

    @else
    <div id="ngd-ccf" style="position:absolute;inset:0;opacity:0;transition:opacity .98s ease;">
        @include('invitation.overlays._curtain-svg', ['side'=>'full','primary'=>$safePrimary,'accent'=>$safeAccent,'ornament'=>$ornament])
    </div>
    @endif

    <div style="position:absolute;inset:0;background:linear-gradient(to top,rgba(0,0,0,0),rgba(0,0,0,.45));pointer-events:none;z-index:1;opacity:0;transition:opacity .6s ease .8s;" id="ngd-cveil"></div>

    <div id="ngd-cui" style="position:absolute;inset:0;display:flex;flex-direction:column;align-items:center;justify-content:center;text-align:center;padding:2rem;z-index:2;opacity:0;transform:translateY(14px);transition:opacity .7s ease 1.2s,transform .7s ease 1.2s;">
        <p style="font-family:'Playfair Display',serif;color:#fff;font-size:1.5rem;margin-bottom:.15rem;">{{ $wedding->bride_name }}</p>
        @if($wedding->bride_nickname)
        <p style="color:rgba(255,255,255,.45);font-size:.8rem;letter-spacing:.08em;margin-bottom:.4rem;">{{ $wedding->bride_nickname }}</p>
        @endif
        <p style="color:{{ $safeAccent }};font-size:1rem;margin-bottom:.4rem;">&amp;</p>
        <p style="font-family:'Playfair Display',serif;color:#fff;font-size:1.5rem;margin-bottom:.15rem;">{{ $wedding->groom_name }}</p>
        @if($wedding->groom_nickname)
        <p style="color:rgba(255,255,255,.45);font-size:.8rem;letter-spacing:.08em;margin-bottom:1.5rem;">{{ $wedding->groom_nickname }}</p>
        @else
        <div style="margin-bottom:1.5rem;"></div>
        @endif
        <p style="color:rgba(255,255,255,.5);font-size:.8rem;line-height:1.7;max-width:20rem;">Merupakan suatu kehormatan dan kebahagiaan bagi kami apabila Bapak/Ibu/Saudara/i berkenan hadir dan memberikan doa restu.</p>
    </div>
</div>
@endif

<style>
@keyframes ngd-swing {
    0%,100% { transform: skewX(.5deg); }
    50%      { transform: skewX(-.5deg); }
}
</style>

<script>
(function () {
    var open  = document.getElementById('ngd-curtain-open');
    var btn   = document.getElementById('ngd-btn-open');
    var veil  = document.getElementById('ngd-veil');
    var ui    = document.getElementById('ngd-ui');
    var close = document.getElementById('ngd-curtain-close');
    if (!open || !btn) return;

    var curtainStyle = open.dataset.curtainStyle || 'panel';

    document.body.style.overflow = 'hidden';

    btn.addEventListener('mouseenter', function () { btn.style.background = 'rgba(255,255,255,.08)'; });
    btn.addEventListener('mouseleave', function () { btn.style.background = 'transparent'; });

    btn.addEventListener('click', function () {
        ui.style.opacity = '0';
        ui.style.transform = 'scale(.95)';
        ui.style.pointerEvents = 'none';
        if (veil) veil.style.opacity = '0';

        if (curtainStyle === 'panel') {
            var cl = document.getElementById('ngd-cl');
            var cr = document.getElementById('ngd-cr');
            if (cl) cl.style.transform = 'translateX(-100%)';
            if (cr) cr.style.transform = 'translateX(100%)';
            // stop swing
            open.querySelectorAll('.ngd-strip').forEach(function (s) { s.style.animation = 'none'; });
        } else if (curtainStyle === 'top') {
            var ct = document.getElementById('ngd-ct');
            if (ct) ct.style.transform = 'translateY(-100%)';
        } else if (curtainStyle === 'bottom') {
            var cb = document.getElementById('ngd-cb');
            if (cb) cb.style.transform = 'translateY(100%)';
        } else {
            var cf = document.getElementById('ngd-cf');
            if (cf) cf.style.opacity = '0';
        }

        setTimeout(function () {
            open.remove();
            document.body.style.overflow = '';
        }, 1100);
    });

    // ── Closing curtain trigger ──────────────────────────────────────────
    if (!close) return;

    var trigger = document.getElementById('ngd-closing-trigger');
    if (!trigger) return;

    var done = false;
    function runClose() {
        if (done) return; done = true;
        close.style.display = 'block';
        close.style.pointerEvents = 'none';

        requestAnimationFrame(function () { requestAnimationFrame(function () {
            var cveil = document.getElementById('ngd-cveil');
            var cui   = document.getElementById('ngd-cui');

            if (curtainStyle === 'panel') {
                var ccl = document.getElementById('ngd-ccl');
                var ccr = document.getElementById('ngd-ccr');
                if (ccl) ccl.style.transform = 'translateX(0)';
                if (ccr) ccr.style.transform = 'translateX(0)';
            } else if (curtainStyle === 'top') {
                var cct = document.getElementById('ngd-cct');
                if (cct) cct.style.transform = 'translateY(0)';
            } else if (curtainStyle === 'bottom') {
                var ccb = document.getElementById('ngd-ccb');
                if (ccb) ccb.style.transform = 'translateY(0)';
            } else {
                var ccf = document.getElementById('ngd-ccf');
                if (ccf) ccf.style.opacity = '1';
            }

            if (cveil) cveil.style.opacity = '1';
            if (cui)   { cui.style.opacity = '1'; cui.style.transform = 'translateY(0)'; }
        }); });
    }

    if ('IntersectionObserver' in window) {
        var obs = new IntersectionObserver(function (entries) {
            if (entries[0].isIntersecting) { runClose(); obs.disconnect(); }
        }, { threshold: 0.1 });
        obs.observe(trigger);
    } else {
        window.addEventListener('scroll', function check() {
            var rect = trigger.getBoundingClientRect();
            if (rect.top <= (window.innerHeight || document.documentElement.clientHeight)) {
                runClose();
                window.removeEventListener('scroll', check);
            }
        }, { passive: true });
    }
})();
</script>
