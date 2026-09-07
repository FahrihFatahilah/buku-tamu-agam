@php
    $heroSection = $sections->firstWhere('section_key', 'hero');
    $heroMedia   = $media->where('collection', 'hero')->first();
    $defaultBg   = '#2C1810';
@endphp

<section id="hero" class="section-bg relative min-h-screen flex flex-col items-center justify-center overflow-hidden">

    @include('templates._section-bg', ['section' => $heroSection, 'defaultBg' => $defaultBg])

    {{-- Hero photo --}}
    @if($heroMedia)
    <div class="absolute inset-0 z-0 overflow-hidden">
        <img src="{{ $heroMedia->url() }}"
            alt="{{ $heroMedia->alt_text ?? $wedding->coupleName() }}"
            class="hero-bg-img parallax-bg w-full h-full object-cover opacity-45"
            data-parallax-speed="0.2">
        <div class="absolute inset-0 bg-gradient-to-b from-black/50 via-transparent to-black/70"></div>
    </div>
    @else
    <div class="absolute inset-0 z-0 bg-[#2C1810]">
        <div class="absolute inset-0 opacity-5"
            style="background-image: repeating-linear-gradient(45deg,#B8960C 0,#B8960C 1px,transparent 0,transparent 50%);background-size:20px 20px;"></div>
    </div>
    @endif

    {{-- Corner ornaments --}}
    <div class="absolute top-6 left-6 w-16 h-16 border-t border-l border-[#B8960C]/40 pointer-events-none z-10"></div>
    <div class="absolute top-6 right-6 w-16 h-16 border-t border-r border-[#B8960C]/40 pointer-events-none z-10"></div>
    <div class="absolute bottom-6 left-6 w-16 h-16 border-b border-l border-[#B8960C]/40 pointer-events-none z-10"></div>
    <div class="absolute bottom-6 right-6 w-16 h-16 border-b border-r border-[#B8960C]/40 pointer-events-none z-10"></div>

    {{-- Content --}}
    <div class="section-content text-center px-6 max-w-lg mx-auto">
        <!-- <p class="text-[#B8960C] text-xs tracking-[0.4em] uppercase mb-8 animate-fade-down delay-200">
            {{ $wedding->appearance['terminology']['wedding_of'] ?? $wedding->template?->default_settings['terminology']['wedding_of'] ?? 'The Wedding of' }}
        </p> -->

        <h1 class="font-serif text-[#F5F0E8] leading-tight animate-cinematic delay-300">
            <span class="block text-2xl sm:text-2xl lg:text-2xl">{{ $wedding->bride_name }}</span>
            <span class="block text-[#B8960C] text-2xl my-3 font-light italic animate-fade-up delay-400">&</span>
            <span class="block text-2xl sm:text-2xl lg:text-2xl">{{ $wedding->groom_name }}</span>
        </h1>


       
    </div>

    {{-- Scroll indicator --}}
    <div class="absolute bottom-8 left-1/2 -translate-x-1/2 z-10 animate-float">
        <svg class="w-5 h-5 text-[#B8960C]/50" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M19 9l-7 7-7-7"/>
        </svg>
    </div>
</section>
