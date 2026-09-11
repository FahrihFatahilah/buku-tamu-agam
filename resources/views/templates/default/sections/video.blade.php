@php $videos = $media->where('collection', 'video')->values(); @endphp
@if($videos->isNotEmpty())
<section id="video" class="py-20 px-6 tpl-panel">
    <div class="max-w-2xl mx-auto">
        <div class="text-center mb-12">
            <p class="text-xs tracking-[0.3em] uppercase tpl-faint mb-3">Video</p>
            <h2 class="tpl-display text-3xl tpl-ink">Momen Bergerak</h2>
        </div>
        <div class="space-y-4">
            @foreach($videos as $video)
            <video controls preload="metadata" class="w-full bg-black">
                <source src="{{ Storage::url($video->file_path) }}" type="{{ $video->mime_type }}">
            </video>
            @endforeach
        </div>
    </div>
</section>
@endif
