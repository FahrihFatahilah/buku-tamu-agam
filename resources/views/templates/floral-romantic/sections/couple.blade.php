<section id="couple" class="py-24 px-6 bg-white">
    <div class="max-w-2xl mx-auto text-center">
        <div class="flex items-center justify-center gap-3 mb-12 reveal">
            <span class="w-12 h-px bg-[#b5606a]/30"></span>
            <svg class="w-5 h-5 text-[#b5606a]/50" fill="currentColor" viewBox="0 0 24 24"><path d="M12 21.593c-5.63-5.539-11-10.297-11-14.402 0-3.791 3.068-5.191 5.281-5.191 1.312 0 4.151.501 5.719 4.457 1.59-3.968 4.464-4.447 5.726-4.447 2.54 0 5.274 1.621 5.274 5.181 0 4.069-5.136 8.625-11 14.402z"/></svg>
            <span class="w-12 h-px bg-[#b5606a]/30"></span>
        </div>
        <div class="grid md:grid-cols-2 gap-16">
            <div class="reveal">
                @php $bridePhoto=$media->where('collection','bride')->first()??$media->where('collection','couple')->first(); @endphp
                @if($bridePhoto)<div class="w-40 h-40 mx-auto mb-5 overflow-hidden rounded-full border-4 border-[#b5606a]/20"><img src="{{ $bridePhoto->url() }}" alt="{{ $wedding->bride_name }}" class="w-full h-full object-cover"></div>@endif
                <p class="font-display text-2xl text-[#2a1a1a] italic">{{ $wedding->bride_name }}</p>
                @if($wedding->bride_nickname && $wedding->bride_nickname !== $wedding->bride_name)<p class="text-[#b5606a] text-sm mt-1">{{ $wedding->bride_nickname }}</p>@endif
                @if($wedding->bride_father || $wedding->bride_mother)<p class="text-[#2a1a1a]/40 text-xs mt-3 leading-relaxed">Putri dari<br>{{ collect([$wedding->bride_father,$wedding->bride_mother])->filter()->join(' & ') }}</p>@endif
            </div>
            <div class="reveal">
                @php $groomPhoto=$media->where('collection','groom')->first()??$media->where('collection','couple')->skip(1)->first(); @endphp
                @if($groomPhoto)<div class="w-40 h-40 mx-auto mb-5 overflow-hidden rounded-full border-4 border-[#7a9e7e]/20"><img src="{{ $groomPhoto->url() }}" alt="{{ $wedding->groom_name }}" class="w-full h-full object-cover"></div>@endif
                <p class="font-display text-2xl text-[#2a1a1a] italic">{{ $wedding->groom_name }}</p>
                @if($wedding->groom_nickname && $wedding->groom_nickname !== $wedding->groom_name)<p class="text-[#7a9e7e] text-sm mt-1">{{ $wedding->groom_nickname }}</p>@endif
                @if($wedding->groom_father || $wedding->groom_mother)<p class="text-[#2a1a1a]/40 text-xs mt-3 leading-relaxed">Putra dari<br>{{ collect([$wedding->groom_father,$wedding->groom_mother])->filter()->join(' & ') }}</p>@endif
            </div>
        </div>
        @if($wedding->description)<p class="mt-12 text-[#2a1a1a]/50 text-sm leading-relaxed max-w-md mx-auto reveal italic">{{ $wedding->description }}</p>@endif
    </div>
</section>
