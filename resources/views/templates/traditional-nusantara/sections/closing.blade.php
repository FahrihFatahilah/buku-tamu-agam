<section id="closing" class="py-24 px-6 bg-[#4a2c0a] text-center">
    <div class="max-w-md mx-auto">
        <div class="w-16 h-px bg-[#c9a84c]/30 mx-auto mb-10"></div>
        <p class="font-display text-3xl text-[#fdf5e4] mb-1">{{ $wedding->bride_name }}</p>
        @if($wedding->bride_nickname)<p class="text-[#c9a84c] text-sm mb-4">{{ $wedding->bride_nickname }}</p>@endif
        <p class="text-[#c9a84c] text-xl mb-4">&</p>
        <p class="font-display text-3xl text-[#fdf5e4] mb-1">{{ $wedding->groom_name }}</p>
        @if($wedding->groom_nickname)<p class="text-[#c9a84c] text-sm mb-8">{{ $wedding->groom_nickname }}</p>@endif
        <p class="text-[#fdf5e4]/30 text-sm leading-relaxed max-w-xs mx-auto">Merupakan suatu kehormatan apabila Bapak/Ibu/Saudara/i berkenan hadir dan memberikan doa restu.</p>
        <div class="flex items-center justify-center gap-3 mt-10">
            <span class="w-8 h-px bg-[#c9a84c]/30"></span>
            <span class="text-[#c9a84c]/40 text-xs tracking-[0.3em]">DAMMMINVITATION</span>
            <span class="w-8 h-px bg-[#c9a84c]/30"></span>
        </div>
    </div>
</section>
