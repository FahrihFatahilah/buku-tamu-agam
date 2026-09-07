<section id="gallery" class="py-20 px-6 bg-[#1a1a1a]">
    <div class="max-w-4xl mx-auto">
        <p class="text-[#c9a96e] text-xs tracking-[0.4em] uppercase mb-12 text-center reveal">Gallery</p>
        @php $galleryMedia = $media->whereIn('collection', ['gallery','prewedding'])->values(); @endphp
        <div class="grid grid-cols-2 sm:grid-cols-3 gap-1">
            @forelse($galleryMedia as $item)
            <div class="aspect-square overflow-hidden reveal">
                <img src="{{ Storage::url($item->file_path) }}" alt="{{ $item->alt_text ?? $wedding->coupleName() }}" class="w-full h-full object-cover grayscale hover:grayscale-0 hover:scale-105 transition-all duration-700" loading="lazy">
            </div>
            @empty
            @for($i=0;$i<6;$i++)<div class="aspect-square bg-white/5 flex items-center justify-center"><span class="text-white/10 text-xs">Foto</span></div>@endfor
            @endforelse
        </div>
    </div>
</section>
