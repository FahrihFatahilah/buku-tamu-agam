<section id="quote" class="py-20 px-6 tpl-panel">
    <div class="max-w-xl mx-auto text-center">
        <div class="w-10 h-px tpl-rule mx-auto mb-10"></div>
        <p class="tpl-display text-xl sm:text-2xl italic leading-relaxed tpl-ink">
            {{ $wedding->quote }}
        </p>
        @if(!empty($wedding->settings['quote_source']))
        <p class="text-xs tracking-widest uppercase tpl-faint mt-6">{{ $wedding->settings['quote_source'] }}</p>
        @endif
        <div class="w-10 h-px tpl-rule mx-auto mt-10"></div>
    </div>
</section>
