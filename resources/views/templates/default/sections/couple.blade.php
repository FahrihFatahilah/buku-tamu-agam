<section class="py-20 px-6 bg-white">
    <div class="max-w-2xl mx-auto text-center">
        <p class="text-xs tracking-[0.3em] uppercase text-stone-400 mb-3">Mempelai</p>
        <h2 class="font-display text-3xl text-stone-800 mb-12">Dengan Penuh Sukacita</h2>

        <div class="space-y-12">
            <div>
                <h3 class="font-display text-2xl text-stone-800">{{ $wedding->bride_name }}</h3>
                @if($wedding->bride_nickname)
                <p class="text-sm text-stone-400 mt-1">{{ $wedding->bride_nickname }}</p>
                @endif
                @if($wedding->bride_father || $wedding->bride_mother)
                <p class="text-sm text-stone-500 mt-3">
                    Putri dari {{ $wedding->bride_father ?: '—' }}@if($wedding->bride_mother) &amp; {{ $wedding->bride_mother }}@endif
                </p>
                @endif
            </div>

            <div class="flex items-center justify-center gap-4">
                <span class="w-12 h-px bg-stone-200"></span>
                <span class="font-display text-xl italic text-stone-400">&amp;</span>
                <span class="w-12 h-px bg-stone-200"></span>
            </div>

            <div>
                <h3 class="font-display text-2xl text-stone-800">{{ $wedding->groom_name }}</h3>
                @if($wedding->groom_nickname)
                <p class="text-sm text-stone-400 mt-1">{{ $wedding->groom_nickname }}</p>
                @endif
                @if($wedding->groom_father || $wedding->groom_mother)
                <p class="text-sm text-stone-500 mt-3">
                    Putra dari {{ $wedding->groom_father ?: '—' }}@if($wedding->groom_mother) &amp; {{ $wedding->groom_mother }}@endif
                </p>
                @endif
            </div>
        </div>
    </div>
</section>
