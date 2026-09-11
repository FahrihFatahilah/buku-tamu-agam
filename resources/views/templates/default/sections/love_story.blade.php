@php $t = $text ?? []; @endphp
@php $stories = collect($section->settings['stories'] ?? []); @endphp
@if($stories->isNotEmpty())
<section id="love_story" class="py-20 px-6 tpl-surface">
    <div class="max-w-lg mx-auto">
        <div class="text-center mb-12">
            <p class="text-xs tracking-[0.3em] uppercase tpl-faint mb-3" data-edit="eyebrow">{{ $t['eyebrow'] ?? 'Perjalanan Kami' }}</p>
            <h2 class="tpl-display text-3xl tpl-ink" data-edit="heading">{{ $t['heading'] ?? 'Kisah Cinta' }}</h2>
        </div>
        <div class="space-y-10">
            @foreach($stories as $story)
            <div class="border-l tpl-hairline pl-6">
                <p class="text-xs tracking-widest uppercase tpl-accent">{{ $story['year'] ?? '' }}</p>
                <p class="tpl-display text-xl tpl-ink mt-1">{{ $story['title'] ?? '' }}</p>
                @if(!empty($story['description']))
                <p class="text-sm tpl-muted mt-2 leading-relaxed">{{ $story['description'] }}</p>
                @endif
            </div>
            @endforeach
        </div>
    </div>
</section>
@endif
