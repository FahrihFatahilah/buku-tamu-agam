<section id="event" class="py-20 px-6 bg-stone-50">
    <div class="max-w-2xl mx-auto">
        <p class="text-stone-400 text-xs tracking-[0.4em] uppercase mb-12 text-center reveal">Rangkaian Acara</p>
        <div class="space-y-3">
            @foreach($events as $event)
            <div class="border-l-2 border-stone-200 pl-5 py-2 reveal" style="transition-delay:{{ $loop->index*100 }}ms">
                <h3 class="font-display text-xl text-stone-900 mb-1">{{ $event->name }}</h3>
                @if($event->starts_at)
                <p class="text-stone-500 text-sm">{{ $event->starts_at->translatedFormat('l, d F Y') }}</p>
                <p class="text-stone-400 text-sm">{{ $event->starts_at->format('H:i') }}@if($event->ends_at) — {{ $event->ends_at->format('H:i') }} WIB@endif</p>
                @endif
                @if($event->venue)<p class="text-stone-500 text-sm mt-2">{{ $event->venue }}</p>@endif
                @if($event->address)<p class="text-stone-300 text-xs mt-1">{{ $event->address }}</p>@endif
                @if($event->maps_url)<a href="{{ $event->maps_url }}" target="_blank" rel="noopener" class="inline-block mt-2 text-xs text-stone-400 hover:text-stone-600 underline underline-offset-2">Lihat Peta →</a>@endif
            </div>
            @endforeach
        </div>
    </div>
</section>
