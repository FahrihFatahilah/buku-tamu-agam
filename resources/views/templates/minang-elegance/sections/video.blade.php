<section id="video" class="py-20 px-6 bg-[#f5ede0]">
    <div class="max-w-3xl mx-auto">
        <div class="text-center mb-10">
            <p class="text-[#c9a84c] text-xs tracking-[0.3em] uppercase mb-3 reveal">Video</p>
            <h2 class="font-serif text-3xl text-[#3d1a1a] reveal">Momen Spesial</h2>
        </div>

        @php
            $videoSection = $sections->firstWhere('section_key', 'video');
            $videoUrl = $videoSection?->settings['url'] ?? null;
            $videoMedia = $media->where('collection', 'video')->first();
        @endphp

        @if($videoUrl)
        <div class="aspect-video reveal">
            @php
                // Support YouTube and Vimeo
                $embedUrl = $videoUrl;
                if (preg_match('/youtube\.com\/watch\?v=([^&]+)/', $videoUrl, $m) ||
                    preg_match('/youtu\.be\/([^?]+)/', $videoUrl, $m)) {
                    $embedUrl = "https://www.youtube.com/embed/{$m[1]}?rel=0";
                } elseif (preg_match('/vimeo\.com\/(\d+)/', $videoUrl, $m)) {
                    $embedUrl = "https://player.vimeo.com/video/{$m[1]}";
                }
            @endphp
            <iframe src="{{ $embedUrl }}" class="w-full h-full"
                frameborder="0" allowfullscreen loading="lazy"
                title="Video Pernikahan {{ $wedding->coupleName() }}">
            </iframe>
        </div>
        @elseif($videoMedia)
        <div class="aspect-video reveal">
            <video controls class="w-full h-full object-cover" preload="none"
                poster="{{ $media->where('collection', 'hero')->first() ? Storage::url($media->where('collection', 'hero')->first()->file_path) : '' }}">
                <source src="{{ Storage::url($videoMedia->file_path) }}" type="{{ $videoMedia->mime_type }}">
            </video>
        </div>
        @endif
    </div>
</section>
