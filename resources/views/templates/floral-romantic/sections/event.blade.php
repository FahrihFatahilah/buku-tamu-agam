<section id="event" class="py-20 px-6 bg-[#f9f0f0]">
    <div class="max-w-2xl mx-auto">
        <p class="text-[#b5606a]/60 text-xs tracking-[0.4em] uppercase mb-12 text-center reveal">Rangkaian Acara</p>
        <div class="space-y-5">
            @foreach($events as $event)
            <div class="bg-white border border-[#b5606a]/10 p-6 reveal" style="transition-delay:{{ $loop->index*100 }}ms">
                <h3 class="font-display text-xl text-[#2a1a1a] italic mb-2">{{ $event->name }}</h3>
                @if($event->starts_at)
                <p class="text-[#b5606a] text-sm mb-1">{{ $event->starts_at->translatedFormat('l, d F Y') }}</p>
                <p class="text-[#2a1a1a]/40 text-sm">{{ $event->starts_at->format('H:i') }}@if($event->ends_at) — {{ $event->ends_at->format('H:i') }} WIB@endif</p>
                @endif
                @if($event->venue)<p class="text-[#2a1a1a]/60 text-sm mt-3">{{ $event->venue }}</p>@endif
                @if($event->address)<p class="text-[#2a1a1a]/40 text-xs mt-1">{{ $event->address }}</p>@endif
                @if($event->dress_code)<p class="text-[#7a9e7e] text-xs mt-3 tracking-wider">Dress Code: {{ $event->dress_code }}</p>@endif
                @if($event->maps_url)<a href="{{ $event->maps_url }}" target="_blank" rel="noopener" class="inline-block mt-3 text-xs text-[#b5606a] border border-[#b5606a]/30 px-3 py-1.5 hover:bg-[#b5606a]/5 transition-colors">Lihat Peta</a>@endif
            </div>
            @endforeach
        </div>
    </div>
</section>
