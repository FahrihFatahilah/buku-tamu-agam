<section id="closing" class="py-24 px-6 bg-white text-center">
    <div class="max-w-md mx-auto">
        <div class="w-8 h-px bg-stone-200 mx-auto mb-10"></div>
        <p class="font-display text-3xl text-stone-900 mb-1">{{ $wedding->bride_name }}</p>
        @if($wedding->bride_nickname)<p class="text-stone-400 text-sm mb-4">{{ $wedding->bride_nickname }}</p>@endif
        <p class="text-stone-300 text-xl mb-4">&</p>
        <p class="font-display text-3xl text-stone-900 mb-1">{{ $wedding->groom_name }}</p>
        @if($wedding->groom_nickname)<p class="text-stone-400 text-sm mb-8">{{ $wedding->groom_nickname }}</p>@endif
        <p class="text-stone-400 text-sm leading-relaxed max-w-xs mx-auto">Merupakan suatu kehormatan apabila Bapak/Ibu/Saudara/i berkenan hadir dan memberikan doa restu.</p>
        <div class="flex items-center justify-center gap-3 mt-10">
            <span class="w-8 h-px bg-stone-200"></span>
            <span class="text-stone-300 text-xs tracking-[0.3em]">DAMMMINVITATION</span>
            <span class="w-8 h-px bg-stone-200"></span>
        </div>
    </div>
</section>
