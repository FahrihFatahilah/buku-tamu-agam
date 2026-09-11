<section id="closing" class="py-24 px-6 bg-[#1a1a1a] text-white">
    <div class="max-w-lg mx-auto text-center">
        <p class="text-xs tracking-[0.3em] uppercase text-white/40 mb-6">Terima Kasih</p>
        <p class="font-display text-2xl sm:text-3xl mb-6">{{ $wedding->coupleName() }}</p>
        <p class="text-white/60 text-sm leading-relaxed">
            Merupakan suatu kehormatan dan kebahagiaan bagi kami apabila Bapak/Ibu/Saudara/i
            berkenan hadir dan memberikan doa restu.
        </p>
        @if($wedding->date)
        <p class="text-white/40 text-xs mt-8 tracking-widest">{{ $wedding->date->translatedFormat('d F Y') }}</p>
        @endif
    </div>
</section>
