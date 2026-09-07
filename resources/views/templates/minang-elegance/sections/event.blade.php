@php $eventSection = $sections->firstWhere('section_key', 'event'); @endphp

<section id="event" class="section-bg relative py-20 px-6">

    @include('templates._section-bg', ['section' => $eventSection, 'defaultBg' => '#2C1810'])

    <div class="section-content max-w-2xl mx-auto">
        <div class="text-center mb-12 reveal">
            <p class="text-[#B8960C] text-xs tracking-[0.3em] uppercase mb-3">Rangkaian Acara</p>
            <span class="ornament-line w-8 h-px bg-[#B8960C]/40 block mx-auto"></span>
        </div>

        <div class="space-y-6">
            @foreach($events as $event)
            <div class="border border-[#B8960C]/20 p-6 reveal" style="transition-delay: {{ $loop->index * 100 }}ms">
                <div class="flex items-start justify-between gap-4">
                    <div class="flex-1">
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
                    </div>

                    @if($event->maps_url)
                    <a href="{{ $event->maps_url }}" target="_blank" rel="noopener"
                        class="shrink-0 px-3 py-1.5 border border-[#B8960C]/40 text-[#B8960C] text-xs hover:bg-[#B8960C]/10 transition-colors">
                        Peta
                    </a>
                    @endif
                </div>
            </div>
            @endforeach
        </div>
    </div>
</section>
