<section id="gallery" class="py-20 px-6 bg-white">
    <div class="max-w-4xl mx-auto">
        <p class="text-[#b5606a]/60 text-xs tracking-[0.4em] uppercase mb-12 text-center reveal">Galeri</p>
        @php $galleryMedia=$media->whereIn('collection',['gallery','prewedding'])->values(); @endphp
        <div class="grid grid-cols-2 sm:grid-cols-3 gap-3">
            @forelse($galleryMedia as $item)
            <div class="aspect-square overflow-hidden rounded-sm reveal"><img src="{{ Storage::url($item->file_path) }}" alt="{{ $item->alt_text??$wedding->coupleName() }}" class="w-full h-full object-cover hover:scale-105 transition-transform duration-700" loading="lazy"></div>
            @empty
            @for($i=0;$i<6;$i++)<div class="aspect-square bg-[#f9f0f0] flex items-center justify-center rounded-sm"><span class="text-[#b5606a]/20 text-xs">Foto</span></div>@endfor
            @endforelse
        </div>
    </div>
</section>
