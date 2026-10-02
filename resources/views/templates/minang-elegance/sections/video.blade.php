<section id="video" class="relative w-full" style="height:100dvh;">

    @php
        $videoSection = $sections->firstWhere('section_key', 'video');
        $videoUrl     = $videoSection?->settings['url'] ?? null;
        $videoMedia   = $media->where('collection', 'video')->first();
    @endphp

    @if($videoUrl)
    @php
        $embedUrl = $videoUrl;
        if (preg_match('/youtube\.com\/watch\?v=([^&]+)/', $videoUrl, $m) ||
            preg_match('/youtu\.be\/([^?]+)/', $videoUrl, $m)) {
            $embedUrl = "https://www.youtube.com/embed/{$m[1]}?rel=0&autoplay=1&mute=1&loop=1&playlist={$m[1]}&controls=0&showinfo=0&modestbranding=1";
        } elseif (preg_match('/vimeo\.com\/(\d+)/', $videoUrl, $m)) {
            $embedUrl = "https://player.vimeo.com/video/{$m[1]}?autoplay=1&muted=1&loop=1&background=1";
        }
    @endphp
    <iframe src="{{ $embedUrl }}"
        class="absolute inset-0 w-full h-full"
        style="border:0;pointer-events:none;"
        allow="autoplay; encrypted-media"
        allowfullscreen
        title="Video Pernikahan {{ $wedding->coupleName() }}">
    </iframe>

    @elseif($videoMedia)
    <video autoplay muted loop playsinline
        class="absolute inset-0 w-full h-full object-cover"
        preload="auto">
        <source src="{{ Storage::url($videoMedia->file_path) }}" type="{{ $videoMedia->mime_type }}">
    </video>
    @endif

    {{-- Overlay gelap tipis agar teks terbaca --}}
    <div class="absolute inset-0 bg-black/30 pointer-events-none"></div>

    {{-- Label tengah --}}
    <div class="absolute inset-0 flex flex-col items-center justify-center text-center px-6 pointer-events-none reveal">
        <p class="text-[#c9a84c] text-xs tracking-[0.4em] uppercase mb-3">Momen Spesial</p>
        <h2 class="font-serif text-3xl text-white">{{ $wedding->coupleName() }}</h2>
    </div>

</section>
