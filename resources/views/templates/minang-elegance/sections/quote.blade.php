@php $quoteSection = $sections->firstWhere('section_key', 'quote'); @endphp

<section id="quote" class="section-bg relative py-20 px-6">

    @include('templates._section-bg', ['section' => $quoteSection, 'defaultBg' => '#F5F0E8'])

    <div class="section-content max-w-xl mx-auto text-center">
        <div class="reveal-blur">
            <svg class="w-8 h-8 mx-auto mb-6 fill-[#B8960C]/30" viewBox="0 0 24 24">
                <path d="M14.017 21v-7.391c0-5.704 3.731-9.57 8.983-10.609l.995 2.151c-2.432.917-3.995 3.638-3.995 5.849h4v10h-9.983zm-14.017 0v-7.391c0-5.704 3.748-9.57 9-10.609l.996 2.151c-2.433.917-3.996 3.638-3.996 5.849h3.983v10h-9.983z"/>
            </svg>
            <blockquote class="font-serif text-lg sm:text-xl text-[#2C1810]/80 leading-relaxed italic">
                {{ $wedding->quote }}
            </blockquote>
        </div>
    </div>
</section>
