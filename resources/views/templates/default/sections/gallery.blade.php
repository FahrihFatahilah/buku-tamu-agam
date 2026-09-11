@php $galleryMedia = $media->whereIn('collection', ['gallery', 'prewedding'])->values(); @endphp
<section class="py-20 px-6 bg-white">
    <div class="max-w-4xl mx-auto">
        <div class="text-center mb-12">
            <p class="text-xs tracking-[0.3em] uppercase text-stone-400 mb-3">Galeri</p>
            <h2 class="font-display text-3xl text-stone-800">Momen Bersama</h2>
        </div>
        @if($galleryMedia->isNotEmpty())
        <div class="grid grid-cols-2 sm:grid-cols-3 gap-2">
            @foreach($galleryMedia as $item)
            <div class="aspect-square overflow-hidden bg-stone-100">
                <img src="{{ Storage::url($item->file_path) }}"
                    alt="{{ $item->alt_text ?: $wedding->coupleName() }}"
                    class="w-full h-full object-cover" loading="lazy">
            </div>
            @endforeach
        </div>
        @else
        <p class="text-center text-sm text-stone-400">Belum ada foto.</p>
        @endif
    </div>
</section>
