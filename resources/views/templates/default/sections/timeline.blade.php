@php $t = $text ?? []; @endphp
@php $items = collect($section->settings['items'] ?? []); @endphp
@if($items->isNotEmpty())
<section id="timeline" class="py-20 px-6 tpl-surface">
    <div class="max-w-lg mx-auto">
        <div class="text-center mb-12">
            <p class="text-xs tracking-[0.3em] uppercase tpl-faint mb-3" data-edit="eyebrow">{{ $t['eyebrow'] ?? 'Susunan Acara' }}</p>
            <h2 class="tpl-display text-3xl tpl-ink" data-edit="heading">{{ $t['heading'] ?? 'Timeline' }}</h2>
        </div>
        <div class="space-y-8">
            @foreach($items as $item)
            <div class="flex gap-6">
                <p class="w-16 shrink-0 text-sm tpl-faint tabular-nums">{{ $item['time'] ?? '' }}</p>
                <div class="border-l tpl-hairline pl-6 pb-2">
                    <p class="tpl-display text-lg tpl-primary">{{ $item['title'] ?? '' }}</p>
                    @if(!empty($item['description']))
                    <p class="text-sm tpl-muted mt-1 leading-relaxed">{{ $item['description'] }}</p>
                    @endif
                </div>
            </div>
            @endforeach
        </div>
    </div>
</section>
@endif
