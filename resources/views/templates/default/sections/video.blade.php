@php $videos = $media->where('collection', 'video')->values(); @endphp
@if($videos->isNotEmpty())
<section class="py-20 px-6 bg-stone-50">
    <div class="max-w-2xl mx-auto">
        <div class="text-center mb-12">
            <p class="text-xs tracking-[0.3em] uppercase text-stone-400 mb-3">Video</p>
            <h2 class="font-display text-3xl text-stone-800">Momen Bergerak</h2>
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
