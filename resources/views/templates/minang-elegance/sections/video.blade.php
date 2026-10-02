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
    {{-- Scale trick: paksa iframe portrait mengisi penuh layar --}}
    <div class="absolute inset-0 overflow-hidden">
        <iframe src="{{ $embedUrl }}"
            class="absolute"
            style="top:50%;left:50%;transform:translate(-50%,-50%);
                   width:100%;height:177.78vw; /* 16/9 portrait */
                   min-width:56.25vh;min-height:100%;
                   border:0;pointer-events:none;"
            allow="autoplay; encrypted-media"
            allowfullscreen
            title="Video Pernikahan {{ $wedding->coupleName() }}">
        </iframe>
    </div>

    @elseif($videoMedia)
    <video autoplay muted loop playsinline
        class="absolute inset-0 w-full h-full object-cover"
        preload="auto">
        <source src="{{ Storage::url($videoMedia->file_path) }}" type="{{ $videoMedia->mime_type }}">
    </video>
    @endif

</section>
