<!DOCTYPE html>
<html lang="id" class="scroll-smooth">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $wedding->seo_title ?? $wedding->coupleName() . ' — Undangan Pernikahan' }}</title>
    <meta name="description" content="{{ $wedding->seo_description ?? $wedding->description }}">
    <meta property="og:title" content="{{ $wedding->seo_title ?? $wedding->coupleName() }}">
    <meta property="og:description" content="{{ $wedding->seo_description ?? $wedding->description }}">
    @if($wedding->og_image)
    <meta property="og:image" content="{{ Storage::url($wedding->og_image) }}">
    @endif
    <meta property="og:type" content="website">
    <link rel="canonical" href="{{ $wedding->publicUrl() }}">
    @if($wedding->favicon)<link rel="icon" href="{{ Storage::url($wedding->favicon) }}">@endif
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:ital,wght@0,400;0,500;0,600;1,400;1,500&family=Lato:wght@300;400;700&family=Scheherazade+New:wght@400;700&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/css/templates/minang-elegance.css', 'resources/js/app.js'])
    <style>
        #curtain-stage {
            position: fixed;
            inset: 0;
            z-index: 9999;
            overflow: hidden;
            background: #1a0a0a;
        }

        /* Panel kiri & kanan — masing-masing 52% agar overlap di tengah */
        .curtain-container {
            position: absolute;
            top: 0; bottom: 0;
            width: 52%;
            overflow: hidden;
            transition: transform 0.98s cubic-bezier(0.77,0,0.18,1);
        }
        #curtain-left  { left: 0; }
        #curtain-right { right: 0; }

        /* SVG ornamen Minang mengisi penuh tiap panel */
        .curtain-container > svg {
            position: absolute;
            inset: 0;
            width: 100%;
            height: 100%;
            display: block;
        }

        /* Strip lipatan overlay di atas SVG */
        .curtain-strips {
            position: absolute;
            inset: 0;
            display: flex;
        }
        .un-curtain {
            flex: 1;
            height: 100%;
            background: repeating-linear-gradient(
                to right,
                rgba(0,0,0,0)          0%,
                rgba(0,0,0,0.22)      28%,
                rgba(255,255,255,0.07) 50%,
                rgba(0,0,0,0.28)      72%,
                rgba(0,0,0,0)        100%
            );
            animation: curtain-swing 2.8s ease-in-out infinite;
        }
        .un-curtain:nth-child(1) { animation-delay:  0.00s; }
        .un-curtain:nth-child(2) { animation-delay: -0.50s; }
        .un-curtain:nth-child(3) { animation-delay: -1.00s; }
        .un-curtain:nth-child(4) { animation-delay: -1.50s; }
        .un-curtain:nth-child(5) { animation-delay: -2.00s; }
        .un-curtain:nth-child(6) { animation-delay: -2.50s; }

        @keyframes curtain-swing {
            0%,100% { transform: skewX(0.5deg);  }
            50%     { transform: skewX(-0.5deg); }
        }

        /* Buka: geser ke samping */
        #curtain-stage.is-open #curtain-left  { transform: translateX(-100%); }
        #curtain-stage.is-open #curtain-right { transform: translateX(100%);  }
        #curtain-stage.is-open .un-curtain    { animation: none; }

        #curtain-overlay {
            position: absolute;
            inset: 0;
            background: linear-gradient(to top, rgba(0,0,0,0) 0%, rgba(0,0,0,0.5) 100%);
            pointer-events: none;
            transition: opacity 0.6s ease;
            z-index: 1;
        }
        #curtain-stage.is-open #curtain-overlay { opacity: 0; }

        #curtain-ui {
            position: absolute;
            inset: 0;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            text-align: center;
            padding: 2rem;
            z-index: 2;
            transition: opacity 0.3s ease, transform 0.3s ease;
        }
        #curtain-stage.is-open #curtain-ui {
            opacity: 0;
            transform: scale(0.95);
            pointer-events: none;
        }

        /* Closing */
        #closing-stage {
            position: fixed;
            inset: 0;
            z-index: 9998;
            pointer-events: none;
            display: none;
            overflow: hidden;
            background: transparent;
        }
        #closing-stage.is-visible { display: block; }
        #closing-stage .curtain-container { transition: transform 0.98s cubic-bezier(0.77,0,0.18,1); }
        #closing-stage #curtain-left  { transform: translateX(-100%); }
        #closing-stage #curtain-right { transform: translateX(100%);  }
        #closing-stage.is-closed #curtain-left  { transform: translateX(0); }
        #closing-stage.is-closed #curtain-right { transform: translateX(0); }

        #closing-overlay {
            position: absolute;
            inset: 0;
            background: linear-gradient(to top, rgba(0,0,0,0) 0%, rgba(0,0,0,0.5) 100%);
            pointer-events: none;
            opacity: 0;
            transition: opacity 0.6s ease 0.8s;
            z-index: 1;
        }
        #closing-stage.is-closed #closing-overlay { opacity: 1; }

        #closing-ui {
            position: absolute;
            inset: 0;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            text-align: center;
            padding: 2rem;
            z-index: 2;
            opacity: 0;
            transform: translateY(14px);
            transition: opacity 0.7s ease 1.2s, transform 0.7s ease 1.2s;
        }
        #closing-stage.is-closed #closing-ui { opacity: 1; transform: translateY(0); }
    </style>
</head>
<body class="bg-[#1a0a0a] text-[#2C1810] antialiased overflow-x-hidden" id="top">

@if($sections->where('section_key', 'opening')->first()?->is_enabled)
<div id="curtain-stage">
    <div id="curtain-left" class="curtain-container">
        @include('templates.minang-elegance._curtain-full')
        <div class="curtain-strips">
            <div class="un-curtain"></div><div class="un-curtain"></div>
            <div class="un-curtain"></div><div class="un-curtain"></div>
            <div class="un-curtain"></div><div class="un-curtain"></div>
        </div>
    </div>
    <div id="curtain-right" class="curtain-container">
        @include('templates.minang-elegance._curtain-full')
        <div class="curtain-strips">
            <div class="un-curtain"></div><div class="un-curtain"></div>
            <div class="un-curtain"></div><div class="un-curtain"></div>
            <div class="un-curtain"></div><div class="un-curtain"></div>
        </div>
    </div>
    <div id="curtain-overlay"></div>
    <div id="curtain-ui">


        <p style="font-family:'Playfair Display',serif;color:rgba(245,240,232,0.7);font-style:italic;font-size:0.85rem;margin-bottom:0.25rem;">Kepada Yth.</p>
        <p style="font-family:'Playfair Display',serif;color:#F5F0E8;font-size:1.2rem;margin-bottom:0.25rem;">@if($guest){{ $guest->name }}@else Tamu Undangan @endif</p>
        <div style="width:2.5rem;height:1px;background:rgba(184,150,12,0.5);margin:1rem auto;"></div>
        <p style="font-family:'Playfair Display',serif;color:#F5F0E8;font-size:1.75rem;line-height:1.2;margin-bottom:0.1rem;">{{ $wedding->bride_name }}</p>
        @if($wedding->bride_nickname)
        <p style="color:rgba(245,240,232,0.5);font-size:0.85rem;margin-bottom:0.5rem;letter-spacing:0.05em;">{{ $wedding->bride_nickname }}</p>
        @endif
        <p style="color:#B8960C;letter-spacing:0.2em;margin-bottom:0.25rem;">&amp;</p>
        <p style="font-family:'Playfair Display',serif;color:#F5F0E8;font-size:1.75rem;line-height:1.2;margin-bottom:0.1rem;">{{ $wedding->groom_name }}</p>
        @if($wedding->groom_nickname)
        <p style="color:rgba(245,240,232,0.5);font-size:0.85rem;margin-bottom:2rem;letter-spacing:0.05em;">{{ $wedding->groom_nickname }}</p>
        @else
        <div style="margin-bottom:2rem;"></div>
        @endif
        <button id="btn-open" style="display:inline-flex;align-items:center;gap:0.5rem;padding:0.75rem 1.75rem;border:1px solid rgba(184,150,12,0.6);color:#B8960C;font-size:0.85rem;letter-spacing:0.15em;background:transparent;cursor:pointer;transition:background 0.2s;">Buka Undangan</button>
    </div>
</div>
@endif

@if($sections->where('section_key', 'closing')->first()?->is_enabled)
<div id="closing-stage">
    <div id="curtain-left" class="curtain-container">
        @include('templates.minang-elegance._curtain-full')
        <div class="curtain-strips">
            <div class="un-curtain"></div><div class="un-curtain"></div>
            <div class="un-curtain"></div><div class="un-curtain"></div>
            <div class="un-curtain"></div><div class="un-curtain"></div>
        </div>
    </div>
    <div id="curtain-right" class="curtain-container">
        @include('templates.minang-elegance._curtain-full')
        <div class="curtain-strips">
            <div class="un-curtain"></div><div class="un-curtain"></div>
            <div class="un-curtain"></div><div class="un-curtain"></div>
            <div class="un-curtain"></div><div class="un-curtain"></div>
        </div>
    </div>
    <div id="closing-overlay"></div>
    <div id="closing-ui">
      
        <p style="font-family:'Playfair Display',serif;color:#F5F0E8;font-size:1.5rem;margin-bottom:0.1rem;">{{ $wedding->bride_name }}</p>
        @if($wedding->bride_nickname)
        <p style="color:rgba(245,240,232,0.5);font-size:0.85rem;margin-bottom:0.5rem;letter-spacing:0.05em;">{{ $wedding->bride_nickname }}</p>
        @endif
        <p style="color:#B8960C;font-size:1.1rem;margin-bottom:0.5rem;">&amp;</p>
        <p style="font-family:'Playfair Display',serif;color:#F5F0E8;font-size:1.5rem;margin-bottom:0.1rem;">{{ $wedding->groom_name }}</p>
        @if($wedding->groom_nickname)
        <p style="color:rgba(245,240,232,0.5);font-size:0.85rem;margin-bottom:2rem;letter-spacing:0.05em;">{{ $wedding->groom_nickname }}</p>
        @else
        <div style="margin-bottom:2rem;"></div>
        @endif
        <p style="color:rgba(245,240,232,0.5);font-size:0.85rem;line-height:1.7;max-width:20rem;">Merupakan suatu kehormatan dan kebahagiaan bagi kami apabila Bapak/Ibu/Saudara/i berkenan hadir dan memberikan doa restu.</p>
        <div style="display:flex;align-items:center;gap:0.75rem;margin-top:2rem;">
            <span style="width:2rem;height:1px;background:rgba(184,150,12,0.4);display:block;"></span>
            <span style="color:rgba(184,150,12,0.5);font-size:0.7rem;letter-spacing:0.2em;">Damm Invitation</span>
            <span style="width:2rem;height:1px;background:rgba(184,150,12,0.4);display:block;"></span>
        </div>
    </div>
</div>
@endif

@if($activePlaylist && $activePlaylist->items->isNotEmpty())
<div class="fixed bottom-5 right-5 z-[200]" x-data="musicPlayer({{ json_encode($activePlaylist) }})">
    <button @click="toggle()" class="w-11 h-11 rounded-full bg-[#7C3238] text-[#F5F0E8] flex items-center justify-center shadow-lg hover:bg-[#6A2A2F] transition-colors focus:outline-none" :aria-label="playing ? 'Jeda musik' : 'Putar musik'">
        <svg x-show="!playing" class="w-4 h-4 ml-0.5" fill="currentColor" viewBox="0 0 24 24"><path d="M8 5v14l11-7z"/></svg>
        <svg x-show="playing" class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24"><path d="M6 19h4V5H6v14zm8-14v14h4V5h-4z"/></svg>
    </button>
    <audio x-ref="audio" :loop="playlist.loop" preload="none"></audio>
</div>
@endif

<div id="invitation-content">
    @include($templateService->sectionView($templateKey, 'hero'))
    @if($sections->where('section_key', 'quote')->first()?->is_enabled)
    @include($templateService->sectionView($templateKey, 'quote'))
    @endif
    @if($sections->where('section_key', 'couple')->first()?->is_enabled)
    @include($templateService->sectionView($templateKey, 'couple'))
    @endif
    
    @if($sections->where('section_key', 'love_story')->first()?->is_enabled)
    @include($templateService->sectionView($templateKey, 'love_story'))
    @endif
    @if($sections->where('section_key', 'countdown')->first()?->is_enabled && $wedding->date)
    @include($templateService->sectionView($templateKey, 'countdown'))
    @endif
    @if($sections->where('section_key', 'event')->first()?->is_enabled && $events->isNotEmpty())
    @include($templateService->sectionView($templateKey, 'event'))
    @endif
    @if($sections->where('section_key', 'venue')->first()?->is_enabled && $events->isNotEmpty())
    @include($templateService->sectionView($templateKey, 'venue'))
    @endif
    @if($sections->where('section_key', 'gallery')->first()?->is_enabled)
    @include($templateService->sectionView($templateKey, 'gallery'))
    @endif
    @if($sections->where('section_key', 'video')->first()?->is_enabled)
    @include($templateService->sectionView($templateKey, 'video'))
    @endif
    @if($sections->where('section_key', 'timeline')->first()?->is_enabled && $events->isNotEmpty())
    @include($templateService->sectionView($templateKey, 'timeline'))
    @endif
    @if($sections->where('section_key', 'rsvp')->first()?->is_enabled && $guest)
    @include($templateService->sectionView($templateKey, 'rsvp'))
    @endif
    @if($sections->where('section_key', 'guest_book')->first()?->is_enabled)
    @include($templateService->sectionView($templateKey, 'guest_book'))
    @endif
    @if($sections->where('section_key', 'gift')->first()?->is_enabled && $giftMethods->isNotEmpty())
    @include($templateService->sectionView($templateKey, 'gift'))
    @endif
    @if($sections->where('section_key', 'closing')->first()?->is_enabled)
    <div id="closing-trigger" style="height:1px;width:100%;"></div>
    @endif
</div>

<script>
(function () {
    var stage = document.getElementById('curtain-stage');
    var btn = document.getElementById('btn-open');
    if (!stage || !btn) return;
    document.body.style.overflow = 'hidden';
    btn.addEventListener('click', function () {
        stage.classList.add('is-open');
        setTimeout(function () { stage.remove(); document.body.style.overflow = ''; }, 1100);
    });
    btn.addEventListener('mouseenter', function () { btn.style.background = 'rgba(184,150,12,0.1)'; });
    btn.addEventListener('mouseleave', function () { btn.style.background = 'transparent'; });
})();
(function () {
    var trigger = document.getElementById('closing-trigger');
    var stage = document.getElementById('closing-stage');
    if (!trigger || !stage) return;
    var done = false;
    function run() {
        if (done) return; done = true;
        stage.classList.add('is-visible');
        requestAnimationFrame(function () { requestAnimationFrame(function () { stage.classList.add('is-closed'); }); });
    }
    // Trigger saat closing section sudah di-scroll ke bawah viewport
    if ('IntersectionObserver' in window) {
        var obs = new IntersectionObserver(function (e) {
            if (e[0].isIntersecting) { run(); obs.disconnect(); }
        }, { threshold: 0, rootMargin: '0px 0px -80% 0px' });
        obs.observe(trigger);
    }
    window.addEventListener('scroll', function check() {
        var rect = trigger.getBoundingClientRect();
        var vh = window.innerHeight || document.documentElement.clientHeight;
        if (rect.top <= vh * 0.2) { run(); window.removeEventListener('scroll', check); }
    }, { passive: true });
})();
function musicPlayer(playlist) {
    return {
        playlist, playing: false, currentIndex: 0,
        init() {
            this.audio = this.$refs.audio;
            this.loadTrack();
            this.audio.addEventListener('ended', () => { if (!this.playlist.loop) this.next(); });
            document.addEventListener('click', () => { if (!this.playing && this.playlist.autoplay) this.play(); }, { once: true });
        },
        loadTrack() { const item = this.playlist.items[this.currentIndex]; if (item) { this.audio.src = item.url ?? `/storage/${item.file_path}`; this.audio.currentTime = item.start_position ?? 0; } },
        toggle() { this.playing ? this.pause() : this.play(); },
        play()   { this.audio.play().then(() => { this.playing = true; }).catch(() => {}); },
        pause()  { this.audio.pause(); this.playing = false; },
        next()   { this.currentIndex = (this.currentIndex + 1) % this.playlist.items.length; this.loadTrack(); if (this.playing) this.play(); },
    };
}
</script>

</body>
</html>
