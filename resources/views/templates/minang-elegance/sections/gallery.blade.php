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
        <div class="grid grid-cols-2 sm:grid-cols-3 gap-2 stagger-children">
            @foreach($galleryMedia as $item)
            <div class="aspect-square overflow-hidden">
                <img src="{{ Storage::url($item->file_path) }}"
                    alt="{{ $item->alt_text ?? $wedding->coupleName() }}"
                    class="w-full h-full object-cover hover:scale-105 transition-transform duration-700 ease-out"
                    loading="lazy">
            </div>
            @endforeach
        </div>
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
