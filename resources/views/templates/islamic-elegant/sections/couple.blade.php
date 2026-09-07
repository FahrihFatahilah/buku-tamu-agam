<section id="couple" class="py-24 px-6 bg-[#fdf8f0]">
    <div class="max-w-2xl mx-auto text-center">
        <div class="flex items-center justify-center gap-3 mb-12 reveal">
            <span class="w-12 h-px bg-[#c9a84c]/40"></span>
            <svg class="w-5 h-5 fill-[#c9a84c]/50" viewBox="0 0 24 24"><path d="M12 2L9.5 9H2l6 4.5L5.5 21 12 16.5 18.5 21 16 13.5l6-4.5h-7.5z"/></svg>
            <span class="w-12 h-px bg-[#c9a84c]/40"></span>
        </div>
        <div class="grid md:grid-cols-2 gap-16">
            <div class="reveal">
                @php $bridePhoto=$media->where('collection','bride')->first()??$media->where('collection','couple')->first(); @endphp
                @if($bridePhoto)<div class="w-40 h-40 mx-auto mb-5 overflow-hidden border-2 border-[#c9a84c]/30"><img src="{{ $bridePhoto->url() }}" alt="{{ $wedding->bride_name }}" class="w-full h-full object-cover"></div>@endif
                <p class="font-display text-2xl text-[#1a4a2e]">{{ $wedding->bride_name }}</p>
                @if($wedding->bride_nickname && $wedding->bride_nickname !== $wedding->bride_name)<p class="text-[#c9a84c] text-sm mt-1 italic">{{ $wedding->bride_nickname }}</p>@endif
                @if($wedding->bride_father || $wedding->bride_mother)<p class="text-[#1a4a2e]/50 text-xs mt-3 leading-relaxed">Putri dari<br>{{ collect([$wedding->bride_father,$wedding->bride_mother])->filter()->join(' & ') }}</p>@endif
            </div>
            <div class="reveal">
                @php $groomPhoto=$media->where('collection','groom')->first()??$media->where('collection','couple')->skip(1)->first(); @endphp
                @if($groomPhoto)<div class="w-40 h-40 mx-auto mb-5 overflow-hidden border-2 border-[#c9a84c]/30"><img src="{{ $groomPhoto->url() }}" alt="{{ $wedding->groom_name }}" class="w-full h-full object-cover"></div>@endif
                <p class="font-display text-2xl text-[#1a4a2e]">{{ $wedding->groom_name }}</p>
                @if($wedding->groom_nickname && $wedding->groom_nickname !== $wedding->groom_name)<p class="text-[#c9a84c] text-sm mt-1 italic">{{ $wedding->groom_nickname }}</p>@endif
                @if($wedding->groom_father || $wedding->groom_mother)<p class="text-[#1a4a2e]/50 text-xs mt-3 leading-relaxed">Putra dari<br>{{ collect([$wedding->groom_father,$wedding->groom_mother])->filter()->join(' & ') }}</p>@endif
            </div>
        </div>
        @if($wedding->description)<p class="mt-12 text-[#1a4a2e]/60 text-sm leading-relaxed max-w-md mx-auto reveal">{{ $wedding->description }}</p>@endif
    </div>
</section>
