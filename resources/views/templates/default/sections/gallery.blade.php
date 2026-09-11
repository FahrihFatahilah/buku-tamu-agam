@php $galleryMedia = $media->whereIn('collection', ['gallery', 'prewedding'])->values(); @endphp
<section id="gallery" class="py-20 px-6 tpl-surface">
    <div class="max-w-4xl mx-auto">
        <div class="text-center mb-12">
            <p class="text-xs tracking-[0.3em] uppercase tpl-faint mb-3">Galeri</p>
            <h2 class="tpl-display text-3xl tpl-ink">Momen Bersama</h2>
        </div>
        @if($galleryMedia->isNotEmpty())
        <div class="grid grid-cols-2 sm:grid-cols-3 gap-2">
            @foreach($galleryMedia as $item)
            <div class="aspect-square overflow-hidden tpl-panel">
                <img src="{{ Storage::url($item->file_path) }}"
                    alt="{{ $item->alt_text ?: $wedding->coupleName() }}"
                    class="w-full h-full object-cover" loading="lazy">
            </div>
            @endforeach
        </div>
        @else
        <p class="text-center text-sm tpl-faint">Belum ada foto.</p>
        @endif
    </div>
</section>
