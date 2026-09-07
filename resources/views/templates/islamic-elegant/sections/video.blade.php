<section id="video" class="py-20 px-6 bg-[#fdf8f0]">
    <div class="max-w-3xl mx-auto">
        <p class="text-[#c9a84c] text-xs tracking-[0.4em] uppercase mb-12 text-center reveal">Video</p>
        @php $videoSection=$sections->firstWhere('section_key','video'); $videoUrl=$videoSection?->settings['url']??null; $videoMedia=$media->where('collection','video')->first(); @endphp
        @if($videoUrl)
        @php if(preg_match('/youtube\.com\/watch\?v=([^&]+)/',$videoUrl,$m)||preg_match('/youtu\.be\/([^?]+)/',$videoUrl,$m)){$embedUrl="https://www.youtube.com/embed/{$m[1]}?rel=0";}elseif(preg_match('/vimeo\.com\/(\d+)/',$videoUrl,$m)){$embedUrl="https://player.vimeo.com/video/{$m[1]}";}else{$embedUrl=$videoUrl;} @endphp
        <div class="aspect-video reveal"><iframe src="{{ $embedUrl }}" class="w-full h-full" frameborder="0" allowfullscreen loading="lazy"></iframe></div>
        @elseif($videoMedia)
        <div class="aspect-video reveal"><video controls class="w-full h-full object-cover" preload="none"><source src="{{ Storage::url($videoMedia->file_path) }}" type="{{ $videoMedia->mime_type }}"></video></div>
        @endif
    </div>
</section>
