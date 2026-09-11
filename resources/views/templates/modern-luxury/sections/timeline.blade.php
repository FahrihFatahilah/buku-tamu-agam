<section id="timeline" class="py-20 px-6 bg-white">
    <div class="max-w-xl mx-auto">
        <p class="text-[#c9a96e] text-xs tracking-[0.4em] uppercase mb-12 text-center reveal">Timeline</p>
        <div class="space-y-0">
            @foreach($events->sortBy('starts_at') as $event)
            <div class="flex gap-6 reveal">
                <div class="flex flex-col items-center">
                    <div class="w-2 h-2 bg-[#c9a96e] shrink-0 mt-1.5"></div>
                    @if(!$loop->last)<div class="w-px flex-1 bg-[#c9a96e]/20 my-1"></div>@endif
                </div>
                <div class="pb-8">
                    @if($event->starts_at)
                    <p class="text-[#c9a96e] text-xs tracking-widest mb-1">{{ $event->starts_at->translatedFormat('d F Y') }} &bull; {{ $event->starts_at->format('H:i') }}@if($event->ends_at) &mdash; {{ $event->ends_at->format('H:i') }} WIB @endif</p>
                    @endif
                    <h3 class="font-display text-lg text-[#1a1a1a] mb-1">{{ $event->name }}</h3>
                    @if($event->venue)<p class="text-stone-400 text-sm">{{ $event->venue }}</p>@endif
                </div>
            </div>
            @endforeach
        </div>
    </div>
</section>
