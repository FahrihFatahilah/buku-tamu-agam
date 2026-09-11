@php
    $timelineSection = $sections->firstWhere('section_key', 'timeline');
    $items           = collect($timelineSection?->settings['items'] ?? []);
@endphp

@if($items->isNotEmpty())
<section id="timeline" class="section-bg relative py-20 px-6">

    @include('templates._section-bg', ['section' => $timelineSection, 'defaultBg' => '#1a0a0a'])

    <div class="section-content max-w-lg mx-auto">
        <div class="text-center mb-12 reveal">
            <p class="text-[#B8960C] text-xs tracking-[0.3em] uppercase mb-3">Susunan Acara</p>
            <h2 class="font-serif text-[#F5F0E8] text-2xl">Timeline</h2>
            <span class="ornament-line w-8 h-px bg-[#B8960C]/40 block mx-auto mt-4"></span>
        </div>

        <div class="space-y-8 stagger-children">
            @foreach($items as $item)
            <div class="flex gap-5">
                <p class="w-14 shrink-0 text-sm text-[#B8960C] tabular-nums pt-0.5">{{ $item['time'] ?? '' }}</p>
                <div class="border-l border-[#B8960C]/25 pl-5 pb-1">
                    <p class="font-serif text-[#F5F0E8] text-lg">{{ $item['title'] ?? '' }}</p>
                    @if(!empty($item['description']))
                    <p class="text-[#F5F0E8]/50 text-sm mt-1 leading-relaxed">{{ $item['description'] }}</p>
                    @endif
                </div>
            </div>
            @endforeach
        </div>
    </div>
</section>
@endif
