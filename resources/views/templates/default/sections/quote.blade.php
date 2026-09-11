<section class="py-20 px-6 bg-stone-50">
    <div class="max-w-xl mx-auto text-center">
        <div class="w-10 h-px bg-stone-300 mx-auto mb-10"></div>
        <p class="font-display text-xl sm:text-2xl italic leading-relaxed text-stone-700">
            {{ $wedding->quote }}
        </p>
        @if(!empty($wedding->settings['quote_source']))
        <p class="text-xs tracking-widest uppercase text-stone-400 mt-6">{{ $wedding->settings['quote_source'] }}</p>
        @endif
        <div class="w-10 h-px bg-stone-300 mx-auto mt-10"></div>
    </div>
</section>
