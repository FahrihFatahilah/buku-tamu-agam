@php $items = collect($sections->firstWhere('section_key', 'timeline')?->settings['items'] ?? []); @endphp
@if($items->isNotEmpty())
<section class="py-20 px-6 bg-white">
    <div class="max-w-lg mx-auto">
        <div class="text-center mb-12">
            <p class="text-xs tracking-[0.3em] uppercase text-stone-400 mb-3">Susunan Acara</p>
            <h2 class="font-display text-3xl text-stone-800">Timeline</h2>
        </div>
        <div class="space-y-8">
            @foreach($items as $item)
            <div class="flex gap-6">
                <p class="w-16 shrink-0 text-sm text-stone-400 tabular-nums">{{ $item['time'] ?? '' }}</p>
                <div class="border-l border-stone-200 pl-6 pb-2">
                    <p class="font-display text-lg text-stone-800">{{ $item['title'] ?? '' }}</p>
                    @if(!empty($item['description']))
                    <p class="text-sm text-stone-500 mt-1 leading-relaxed">{{ $item['description'] }}</p>
                    @endif
                </div>
            </div>
            @endforeach
        </div>
    </div>
</section>
@endif
