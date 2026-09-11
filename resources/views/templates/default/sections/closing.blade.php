@php $t = $text ?? []; @endphp
<section id="closing" class="py-24 px-6 tpl-invert">
    <div class="max-w-lg mx-auto text-center">
        <p class="text-xs tracking-[0.3em] uppercase opacity-40 mb-6" data-edit="eyebrow">{{ $t['eyebrow'] ?? 'Terima Kasih' }}</p>
        <p class="tpl-display text-2xl sm:text-3xl mb-6">{{ $wedding->coupleName() }}</p>
        <p class="text-sm leading-relaxed opacity-60" data-edit="body">{{ $t['body'] ?? 'Merupakan suatu kehormatan dan kebahagiaan bagi kami apabila Bapak/Ibu/Saudara/i berkenan hadir dan memberikan doa restu.' }}</p>
        @if($wedding->date)
        <p class="text-xs mt-8 tracking-widest opacity-40">{{ $wedding->date->translatedFormat('d F Y') }}</p>
        @endif
    </div>
</section>
