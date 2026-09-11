@php $stories = collect($sections->firstWhere('section_key', 'love_story')?->settings['stories'] ?? []); @endphp
@if($stories->isNotEmpty())
<section class="py-20 px-6 bg-white">
    <div class="max-w-lg mx-auto">
        <div class="text-center mb-12">
            <p class="text-xs tracking-[0.3em] uppercase text-stone-400 mb-3">Perjalanan Kami</p>
            <h2 class="font-display text-3xl text-stone-800">Kisah Cinta</h2>
        </div>
        <div class="space-y-10">
            @foreach($stories as $story)
            <div class="border-l border-stone-200 pl-6">
                <p class="text-xs tracking-widest uppercase text-stone-400">{{ $story['year'] ?? '' }}</p>
                <p class="font-display text-xl text-stone-800 mt-1">{{ $story['title'] ?? '' }}</p>
                @if(!empty($story['description']))
                <p class="text-sm text-stone-500 mt-2 leading-relaxed">{{ $story['description'] }}</p>
                @endif
            </div>
            @endforeach
        </div>
    </div>
</section>
@endif
