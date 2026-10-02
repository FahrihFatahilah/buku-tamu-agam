@php
    $gallerySection = $sections->firstWhere('section_key', 'gallery');
    $galleryMedia   = $media->whereIn('collection', ['gallery', 'prewedding'])->values();
@endphp

<section id="gallery" class="section-bg relative py-20 px-6">

    @include('templates._section-bg', ['section' => $gallerySection, 'defaultBg' => '#1a0a0a'])

    <div class="section-content max-w-4xl mx-auto">
        <div class="text-center mb-12 reveal">
            <p class="text-[#c9a84c] text-xs tracking-[0.3em] uppercase mb-3">Galeri</p>
            <h2 class="font-serif text-3xl text-[#f5ede0]">Momen Bersama</h2>
        </div>

        @if($galleryMedia->isNotEmpty())
        <div class="grid grid-cols-2 sm:grid-cols-3 gap-2 stagger-children" id="gallery-grid">
            @foreach($galleryMedia as $i => $item)
            <div class="aspect-square overflow-hidden cursor-zoom-in gallery-item"
                 data-src="{{ Storage::url($item->file_path) }}"
                 data-index="{{ $i }}"
                 onclick="openLightbox({{ $i }})">
                <img src="{{ Storage::url($item->file_path) }}"
                    alt="{{ $item->alt_text ?? $wedding->coupleName() }}"
                    class="w-full h-full object-cover transition-transform duration-700 ease-out"
                    style="transform-origin:center;"
                    loading="lazy">
            </div>
            @endforeach
        </div>

        {{-- Lightbox --}}
        <div id="lightbox" class="fixed inset-0 z-[9000] bg-black/95 flex flex-col items-center justify-center hidden"
             onclick="if(event.target===this)closeLightbox()">

            {{-- Close --}}
            <button onclick="closeLightbox()"
                class="absolute top-4 right-4 w-10 h-10 flex items-center justify-center text-white/60 active:text-white z-20 text-2xl">&times;</button>

            {{-- Counter --}}
            <div id="lb-counter" class="absolute top-4 left-1/2 -translate-x-1/2 text-white/40 text-xs tracking-widest z-20"></div>

            {{-- Image wrapper (swipeable) --}}
            <div id="lb-wrap" class="relative w-full flex items-center justify-center" style="height:80vh;touch-action:pan-y;">
                <img id="lightbox-img" src="" alt=""
                     class="max-h-full max-w-[92vw] object-contain select-none"
                     style="transition:opacity 0.22s ease,transform 0.22s ease;">
            </div>

            {{-- Nav arrows --}}
            <button onclick="lightboxPrev()"
                class="absolute left-2 top-1/2 -translate-y-1/2 w-10 h-10 flex items-center justify-center text-white/50 active:text-white text-3xl z-20">&#8249;</button>
            <button onclick="lightboxNext()"
                class="absolute right-2 top-1/2 -translate-y-1/2 w-10 h-10 flex items-center justify-center text-white/50 active:text-white text-3xl z-20">&#8250;</button>

            {{-- Dot indicators --}}
            <div id="lb-dots" class="absolute bottom-5 left-1/2 -translate-x-1/2 flex gap-1.5 z-20"></div>
        </div>

        <script>
        var _galleryUrls = @json($galleryMedia->map(fn($m) => Storage::url($m->file_path))->values());
        var _lbIndex = 0;

        function _lbShow(i, dir) {
            _lbIndex = (i + _galleryUrls.length) % _galleryUrls.length;
            var img = document.getElementById('lightbox-img');
            var slideOut = dir === 1 ? '-40px' : '40px';
            var slideIn  = dir === 1 ? '40px'  : '-40px';
            img.style.opacity = '0';
            img.style.transform = 'translateX(' + slideOut + ') scale(0.96)';
            setTimeout(function () {
                img.src = _galleryUrls[_lbIndex];
                img.style.transform = 'translateX(' + slideIn + ') scale(0.96)';
                img.onload = function () {
                    img.style.opacity = '1';
                    img.style.transform = 'translateX(0) scale(1)';
                };
                /* jika sudah cache, onload tidak fire */
                if (img.complete) { img.style.opacity='1'; img.style.transform='translateX(0) scale(1)'; }
            }, 180);
            /* counter */
            document.getElementById('lb-counter').textContent = (_lbIndex + 1) + ' / ' + _galleryUrls.length;
            /* dots */
            var dots = document.getElementById('lb-dots');
            Array.from(dots.children).forEach(function(d, idx) {
                d.style.background = idx === _lbIndex ? 'rgba(184,150,12,0.9)' : 'rgba(255,255,255,0.25)';
            });
        }

        function openLightbox(i) {
            var lb = document.getElementById('lightbox');
            lb.classList.remove('hidden');
            document.body.style.overflow = 'hidden';
            /* build dots once */
            var dots = document.getElementById('lb-dots');
            if (!dots.children.length) {
                _galleryUrls.forEach(function(_, idx) {
                    var d = document.createElement('div');
                    d.style.cssText = 'width:6px;height:6px;border-radius:50%;cursor:pointer;transition:background 0.2s;';
                    d.onclick = function(e) { e.stopPropagation(); _lbShow(idx, idx > _lbIndex ? 1 : -1); };
                    dots.appendChild(d);
                });
            }
            _lbShow(i, 0);
        }

        function closeLightbox() {
            document.getElementById('lightbox').classList.add('hidden');
            document.body.style.overflow = '';
        }
        function lightboxPrev() { _lbShow(_lbIndex - 1, -1); }
        function lightboxNext() { _lbShow(_lbIndex + 1,  1); }

        /* Keyboard */
        document.addEventListener('keydown', function(e) {
            if (document.getElementById('lightbox').classList.contains('hidden')) return;
            if (e.key === 'ArrowLeft')  lightboxPrev();
            if (e.key === 'ArrowRight') lightboxNext();
            if (e.key === 'Escape')     closeLightbox();
        });

        /* Touch swipe */
        (function () {
            var wrap = document.getElementById('lb-wrap');
            var startX = 0, startY = 0, dragging = false;
            wrap.addEventListener('touchstart', function(e) {
                startX = e.touches[0].clientX;
                startY = e.touches[0].clientY;
                dragging = true;
            }, { passive: true });
            wrap.addEventListener('touchend', function(e) {
                if (!dragging) return;
                dragging = false;
                var dx = e.changedTouches[0].clientX - startX;
                var dy = e.changedTouches[0].clientY - startY;
                if (Math.abs(dx) < 40 || Math.abs(dx) < Math.abs(dy)) return;
                dx < 0 ? lightboxNext() : lightboxPrev();
            }, { passive: true });
        })();

        /* Hover zoom grid items */
        document.querySelectorAll('.gallery-item img').forEach(function(img) {
            img.parentElement.addEventListener('mouseenter', function() { img.style.transform = 'scale(1.08)'; });
            img.parentElement.addEventListener('mouseleave', function() { img.style.transform = 'scale(1)'; });
        });
        </script>
        @else
        <div class="grid grid-cols-2 sm:grid-cols-3 gap-2 stagger-children">
            @for($i = 0; $i < 6; $i++)
            <div class="aspect-square bg-[#2a1a1a] flex items-center justify-center">
                <span class="text-[#c9a84c]/20 text-xs">Foto</span>
            </div>
            @endfor
        </div>
        @endif
    </div>
</section>
