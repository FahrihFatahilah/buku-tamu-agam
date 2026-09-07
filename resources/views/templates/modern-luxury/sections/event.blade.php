<section id="event" class="py-20 px-6 bg-[#1a1a1a]">
    <div class="max-w-2xl mx-auto">
        <p class="text-[#c9a96e] text-xs tracking-[0.4em] uppercase mb-12 text-center reveal">Rangkaian Acara</p>
        <div class="space-y-4">
            @foreach($events as $event)
            <div class="border border-white/10 p-6 reveal" style="transition-delay: {{ $loop->index * 100 }}ms">
                <div class="flex items-start justify-between gap-4">
                    <div class="flex-1">
                        <h3 class="font-display text-xl text-white mb-2">{{ $event->name }}</h3>
                        @if($event->starts_at)
                        <p class="text-[#c9a96e] text-sm mb-1">{{ $event->starts_at->translatedFormat('l, d F Y') }}</p>
                        <p class="text-white/40 text-sm">{{ $event->starts_at->format('H:i') }}@if($event->ends_at) — {{ $event->ends_at->format('H:i') }} WIB@endif</p>
                        @endif
                        @if($event->venue)<p class="text-white/50 text-sm mt-3">{{ $event->venue }}</p>@endif
                        @if($event->address)<p class="text-white/30 text-xs mt-1">{{ $event->address }}</p>@endif
                        @if($event->dress_code)<p class="text-[#c9a96e]/50 text-xs mt-3 tracking-wider">Dress Code: {{ $event->dress_code }}</p>@endif
                    </div>
                    @if($event->maps_url)
                    <a href="{{ $event->maps_url }}" target="_blank" rel="noopener" class="shrink-0 px-3 py-1.5 border border-white/20 text-white/50 text-xs hover:border-[#c9a96e]/50 hover:text-[#c9a96e] transition-colors">Peta</a>
                    @endif
                </div>
            </div>
            @endforeach
        </div>
    </div>
</section>
