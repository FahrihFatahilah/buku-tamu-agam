@php
    $quoteSection = $sections->firstWhere('section_key', 'quote');
    $arabic  = $wedding->settings['quote_arabic'] ?? 'وَمِنْ آيَاتِهِ أَنْ خَلَقَ لَكُم مِّنْ أَنفُسِكُمْ أَزْوَاجًا لِّتَسْكُنُوا إِلَيْهَا';
    $source  = $wedding->settings['quote_source'] ?? 'QS. Ar-Rum: 21';
    // Terjemahan: pakai $wedding->quote hanya jika bukan isi sumber ayat
    $isQuoteActuallySource = $wedding->quote && preg_match('/^(Q\.?S\.?|Surah|QS)/i', trim($wedding->quote));
    $transl  = (!$wedding->quote || $isQuoteActuallySource)
        ? 'Dan di antara tanda-tanda kekuasaan-Nya ialah Dia menciptakan untukmu pasangan hidup dari jenismu sendiri supaya kamu cenderung dan merasa tenteram kepadanya.'
        : $wedding->quote;
@endphp

<section id="quote" class="section-bg relative py-20 px-6">

    @include('templates._section-bg', ['section' => $quoteSection, 'defaultBg' => '#F5F0E8'])

    <div class="section-content max-w-xl mx-auto text-center">
        <div class="reveal-blur">

            {{-- Teks Arab — font kaligrafi --}}
            <p class="mb-4 leading-loose text-[#2C1810]"
                dir="rtl"
                style="font-family:'Scheherazade New','Amiri','Traditional Arabic',serif; font-size:1.6rem; line-height:2.2;">
                {{ $arabic }}
            </p>

            {{-- Divider --}}
            <div class="flex items-center justify-center gap-3 mb-4">
                <span class="w-8 h-px bg-[#B8960C]/40 block"></span>
                <span class="w-1.5 h-1.5 rounded-full bg-[#B8960C]/40"></span>
                <span class="w-8 h-px bg-[#B8960C]/40 block"></span>
            </div>

            {{-- Terjemahan — font serif latin --}}
            <blockquote class="font-serif text-base sm:text-lg text-[#2C1810]/75 leading-relaxed italic mb-4"
                style="font-family:'Playfair Display',Georgia,serif;">
                {{ $transl }}
            </blockquote>

            {{-- Sumber --}}
            <p class="text-[#B8960C] text-xs tracking-[0.2em] uppercase">{{ $source }}</p>

        </div>
    </div>
</section>
