@php
    $coupleSection = $sections->firstWhere('section_key', 'couple');
@endphp

<section id="couple" class="section-bg relative py-20 px-6">

    @include('templates._section-bg', ['section' => $coupleSection, 'defaultBg' => '#F5F0E8'])

    <div class="section-content max-w-2xl mx-auto text-center">

        {{-- Ornament divider --}}
        <div class="flex items-center justify-center gap-3 mb-12 reveal">
            <span class="ornament-line w-12 h-px bg-[#B8960C]/40 block"></span>
            <svg viewBox="0 0 24 24" class="w-4 h-4 fill-[#B8960C]/60 animate-scale-in">
                <path d="M12 2L9.5 9H2l6 4.5L5.5 21 12 16.5 18.5 21 16 13.5l6-4.5h-7.5z"/>
            </svg>
            <span class="ornament-line w-12 h-px bg-[#B8960C]/40 block"></span>
        </div>

        <div class="grid md:grid-cols-2 gap-12 md:gap-8 stagger-children">
            {{-- Groom --}}
           

            {{-- Bride --}}
            <div>
                @php $bridePhoto = $media->where('collection', 'bride')->skip(1)->first() ?? $media->where('collection', 'couple')->skip(1)->first(); @endphp
                @if($bridePhoto)
                <div class="w-40 h-40 mx-auto mb-5 overflow-hidden border-2 border-[#B8960C]/30 reveal-scale">
                    <img src="{{ $bridePhoto->url() }}" alt="{{ $wedding->bride_name }}" class="w-full h-full object-cover">
                </div>
                @endif
                <p class="font-serif text-2xl text-[#2C1810]">{{ $wedding->bride_name }}</p>
                @if($wedding->bride_nickname && $wedding->bride_nickname !== $wedding->bride_name)
                <p class="text-[#7C3238] text-sm mt-1 italic">"{{ $wedding->bride_nickname }}"</p>
                @endif
                @if($wedding->bride_father || $wedding->bride_mother)
                <p class="text-[#2C1810]/50 text-xs mt-3 leading-relaxed">
                    Putri dari<br>
                    {{ collect([$wedding->bride_father, $wedding->bride_mother])->filter()->join(' & ') }}
                </p>
                @endif
            </div>
             <div>
                @php $groomPhoto = $media->where('collection', 'groom')->first() ?? $media->where('collection', 'couple')->first(); @endphp
                @if($groomPhoto)
                <div class="w-40 h-40 mx-auto mb-5 overflow-hidden border-2 border-[#B8960C]/30 reveal-scale">
                    <img src="{{ $groomPhoto->url() }}" alt="{{ $wedding->groom_name }}" class="w-full h-full object-cover">
                </div>
                @endif
                <p class="font-serif text-2xl text-[#2C1810]">{{ $wedding->groom_name }}</p>
                @if($wedding->groom_nickname && $wedding->groom_nickname !== $wedding->groom_name)
                <p class="text-[#7C3238] text-sm mt-1 italic">"{{ $wedding->groom_nickname }}"</p>
                @endif
                @if($wedding->groom_father || $wedding->groom_mother)
                <p class="text-[#2C1810]/50 text-xs mt-3 leading-relaxed">
                    Putra dari<br>
                    {{ collect([$wedding->groom_father, $wedding->groom_mother])->filter()->join(' & ') }}
                </p>
                @endif
            </div>
        </div>

        @if($wedding->description)
        <div class="mt-12 max-w-lg mx-auto reveal">
            <p class="text-[#2C1810]/70 text-sm leading-relaxed">{{ $wedding->description }}</p>
        </div>
        @endif
    </div>
</section>
