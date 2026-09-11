@php
    $p = $node['props'] ?? [];
    $videos = $media->where('collection', 'video')->values();
@endphp
@if($videos->isNotEmpty())
<div class="n-inner">
    <div class="text-center mb-12">
        @if(!empty($p['eyebrow']))
        <p class="text-xs tracking-[0.3em] uppercase opacity-60 mb-3" data-edit-prop="eyebrow">{{ $p['eyebrow'] }}</p>
        @endif
        @if(!empty($p['heading']))
        <h2 class="n-display text-3xl" data-edit-prop="heading">{{ $p['heading'] }}</h2>
        @endif
    </div>

    <div class="space-y-4">
        @foreach($videos as $video)
        <video controls preload="metadata" class="w-full" style="background: #000;">
            <source src="{{ Storage::url($video->file_path) }}" type="{{ $video->mime_type }}">
        </video>
        @endforeach
    </div>
</div>
@endif
