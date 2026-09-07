<section id="closing" class="py-24 px-6 bg-[#f9f0f0] text-center">
    <div class="max-w-md mx-auto">
        <svg class="w-10 h-10 mx-auto mb-8 text-[#b5606a]/30" fill="currentColor" viewBox="0 0 24 24"><path d="M12 21.593c-5.63-5.539-11-10.297-11-14.402 0-3.791 3.068-5.191 5.281-5.191 1.312 0 4.151.501 5.719 4.457 1.59-3.968 4.464-4.447 5.726-4.447 2.54 0 5.274 1.621 5.274 5.181 0 4.069-5.136 8.625-11 14.402z"/></svg>
        <p class="font-display text-3xl text-[#2a1a1a] italic mb-1">{{ $wedding->bride_name }}</p>
        @if($wedding->bride_nickname)<p class="text-[#b5606a] text-sm mb-4">{{ $wedding->bride_nickname }}</p>@endif
        <p class="text-[#b5606a] text-xl mb-4">&</p>
        <p class="font-display text-3xl text-[#2a1a1a] italic mb-1">{{ $wedding->groom_name }}</p>
        @if($wedding->groom_nickname)<p class="text-[#7a9e7e] text-sm mb-8">{{ $wedding->groom_nickname }}</p>@endif
        <p class="text-[#2a1a1a]/40 text-sm leading-relaxed max-w-xs mx-auto">Merupakan suatu kehormatan apabila Bapak/Ibu/Saudara/i berkenan hadir dan memberikan doa restu.</p>
        <div class="flex items-center justify-center gap-3 mt-10">
            <span class="w-8 h-px bg-[#b5606a]/30"></span>
            <span class="text-[#b5606a]/40 text-xs tracking-[0.3em]">DAMMMINVITATION</span>
            <span class="w-8 h-px bg-[#b5606a]/30"></span>
        </div>
    </div>
</section>
