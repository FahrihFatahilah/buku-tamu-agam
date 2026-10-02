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
        <div id="lightbox" class="fixed inset-0 z-[9000] bg-black/95 flex items-center justify-center hidden"
             onclick="if(event.target===this)closeLightbox()">
            <button onclick="closeLightbox()" class="absolute top-4 right-4 text-white/60 hover:text-white text-2xl leading-none z-10">&times;</button>
            <button onclick="lightboxPrev()" class="absolute left-3 top-1/2 -translate-y-1/2 text-white/50 hover:text-white text-3xl px-3 z-10">&#8249;</button>
            <button onclick="lightboxNext()" class="absolute right-3 top-1/2 -translate-y-1/2 text-white/50 hover:text-white text-3xl px-3 z-10">&#8250;</button>
            <img id="lightbox-img" src="" alt="" class="max-h-[90vh] max-w-[90vw] object-contain select-none"
                 style="transition:opacity 0.25s ease,transform 0.25s ease;">
        </div>
        @php $galleryUrls = $galleryMedia->map(fn($m) => Storage::url($m->file_path))->values()->toJson(); @endphp
        <script>
        var _galleryUrls = @json($galleryMedia->map(fn($m) => Storage::url($m->file_path))->values());
        var _lbIndex = 0;
        function openLightbox(i) {
            _lbIndex = i;
            var lb = document.getElementById('lightbox');
            var img = document.getElementById('lightbox-img');
            img.style.opacity = '0'; img.style.transform = 'scale(0.92)';
            img.src = _galleryUrls[i];
            lb.classList.remove('hidden');
            document.body.style.overflow = 'hidden';
            img.onload = function() { img.style.opacity='1'; img.style.transform='scale(1)'; };
        }
        function closeLightbox() {
            document.getElementById('lightbox').classList.add('hidden');
            document.body.style.overflow = '';
        }
        function lightboxPrev() { openLightbox((_lbIndex - 1 + _galleryUrls.length) % _galleryUrls.length); }
        function lightboxNext() { openLightbox((_lbIndex + 1) % _galleryUrls.length); }
        document.addEventListener('keydown', function(e) {
            if (document.getElementById('lightbox').classList.contains('hidden')) return;
            if (e.key === 'ArrowLeft') lightboxPrev();
            if (e.key === 'ArrowRight') lightboxNext();
            if (e.key === 'Escape') closeLightbox();
        });
        /* hover zoom per item */
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
