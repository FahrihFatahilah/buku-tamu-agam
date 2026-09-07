<section id="couple" class="py-24 px-6 bg-white">
    <div class="max-w-2xl mx-auto text-center">
        <div class="w-16 h-px bg-[#c9a96e]/40 mx-auto mb-12"></div>
        <div class="grid md:grid-cols-2 gap-16">
            <div class="reveal">
                @php $bridePhoto = $media->where('collection', 'bride')->first() ?? $media->where('collection', 'couple')->first(); @endphp
                @if($bridePhoto)
                <div class="w-36 h-36 mx-auto mb-5 overflow-hidden grayscale hover:grayscale-0 transition-all duration-700">
                    <img src="{{ $bridePhoto->url() }}" alt="{{ $wedding->bride_name }}" class="w-full h-full object-cover">
                </div>
                @endif
                <p class="font-display text-2xl text-[#1a1a1a]">{{ $wedding->bride_name }}</p>
                @if($wedding->bride_nickname && $wedding->bride_nickname !== $wedding->bride_name)
                <p class="text-[#c9a96e] text-sm mt-1 tracking-wider">{{ $wedding->bride_nickname }}</p>
                @endif
                @if($wedding->bride_father || $wedding->bride_mother)
                <p class="text-stone-400 text-xs mt-3 leading-relaxed">Putri dari<br>{{ collect([$wedding->bride_father, $wedding->bride_mother])->filter()->join(' & ') }}</p>
                @endif
            </div>
            <div class="reveal">
                @php $groomPhoto = $media->where('collection', 'groom')->first() ?? $media->where('collection', 'couple')->skip(1)->first(); @endphp
                @if($groomPhoto)
                <div class="w-36 h-36 mx-auto mb-5 overflow-hidden grayscale hover:grayscale-0 transition-all duration-700">
                    <img src="{{ $groomPhoto->url() }}" alt="{{ $wedding->groom_name }}" class="w-full h-full object-cover">
                </div>
                @endif
                <p class="font-display text-2xl text-[#1a1a1a]">{{ $wedding->groom_name }}</p>
                @if($wedding->groom_nickname && $wedding->groom_nickname !== $wedding->groom_name)
                <p class="text-[#c9a96e] text-sm mt-1 tracking-wider">{{ $wedding->groom_nickname }}</p>
                @endif
                @if($wedding->groom_father || $wedding->groom_mother)
                <p class="text-stone-400 text-xs mt-3 leading-relaxed">Putra dari<br>{{ collect([$wedding->groom_father, $wedding->groom_mother])->filter()->join(' & ') }}</p>
                @endif
            </div>
        </div>
        @if($wedding->description)
        <p class="mt-12 text-stone-500 text-sm leading-relaxed max-w-md mx-auto reveal">{{ $wedding->description }}</p>
        @endif
        <div class="w-16 h-px bg-[#c9a96e]/40 mx-auto mt-12"></div>
    </div>
</section>
