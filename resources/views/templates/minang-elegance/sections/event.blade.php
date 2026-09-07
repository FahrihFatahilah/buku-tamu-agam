@php $eventSection = $sections->firstWhere('section_key', 'event'); @endphp

<section id="event" class="section-bg relative py-20 px-6">

    @include('templates._section-bg', ['section' => $eventSection, 'defaultBg' => '#2C1810'])

    <div class="section-content max-w-2xl mx-auto">
        <div class="text-center mb-12 reveal">
            <p class="text-[#B8960C] text-xs tracking-[0.3em] uppercase mb-3">Rangkaian Acara</p>
            <span class="ornament-line w-8 h-px bg-[#B8960C]/40 block mx-auto"></span>
        </div>

        <div class="space-y-10">
            @foreach($events as $event)
            <div class="reveal" style="transition-delay: {{ $loop->index * 100 }}ms">
                {{-- Info acara --}}
                <div class="border border-[#B8960C]/20 p-6 mb-0">
                    <h3 class="font-serif text-[#F5F0E8] text-xl mb-2">{{ $event->name }}</h3>

                    @if($event->starts_at)
                    <p class="text-[#B8960C] text-sm mb-1">
                        {{ $event->starts_at->translatedFormat('l, d F Y') }}
                    </p>
                    <p class="text-[#F5F0E8]/50 text-sm">
                        {{ $event->starts_at->format('H:i') }}
                        @if($event->ends_at) — {{ $event->ends_at->format('H:i') }} WIB @endif
                    </p>
                    @endif

                    @if($event->venue)
                    <p class="text-[#F5F0E8]/60 text-sm mt-3">{{ $event->venue }}</p>
                    @endif

                    @if($event->address)
                    <p class="text-[#F5F0E8]/40 text-xs mt-1">{{ $event->address }}</p>
                    @endif

                    @if($event->dress_code)
                    <p class="text-[#B8960C]/70 text-xs mt-3 tracking-wider">Dress Code: {{ $event->dress_code }}</p>
                    @endif

                    @if($event->maps_url && !$event->maps_embed)
                    <a href="{{ $event->maps_url }}" target="_blank" rel="noopener"
                        class="inline-flex items-center gap-1.5 mt-4 text-xs text-[#B8960C] border border-[#B8960C]/40 px-3 py-1.5 hover:bg-[#B8960C]/10 transition-colors">
                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/>
                        </svg>
                        Buka di Google Maps
                    </a>
                    @endif
                </div>

                {{-- Embedded map langsung di bawah acara --}}
                @if($event->maps_embed)
                <div class="w-full border border-[#B8960C]/20 border-t-0 overflow-hidden" style="height:260px;">
                    <div style="width:100%;height:100%;position:relative;">
                        {!! preg_replace('/width="[^"]*"/', 'width="100%"', preg_replace('/height="[^"]*"/', 'height="100%"', $event->maps_embed)) !!}
                    </div>
                </div>
                @if($event->maps_url)
                <a href="{{ $event->maps_url }}" target="_blank" rel="noopener"
                    class="flex items-center justify-center gap-1.5 py-2 border border-[#B8960C]/20 border-t-0 text-xs text-[#B8960C]/70 hover:text-[#B8960C] hover:bg-[#B8960C]/5 transition-colors">
                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/>
                    </svg>
                    Buka di Google Maps
                </a>
                @endif
                @endif
            </div>
            @endforeach
        </div>
    </div>
</section>
