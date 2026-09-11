{{--
    Generic section fallback.

    Reached only when neither the wedding's template nor templates/default
    provides a view for a requested section key. Rendering this keeps a
    misconfigured template from throwing a 500 on a live invitation.
--}}
<section class="py-20 px-6 bg-white">
    <div class="max-w-lg mx-auto text-center">
        <div class="w-10 h-px bg-stone-300 mx-auto mb-8"></div>
        <h2 class="font-display text-2xl text-stone-800 mb-3">{{ $wedding->coupleName() }}</h2>
        @if($wedding->date)
        <p class="text-stone-500 text-sm">{{ $wedding->date->translatedFormat('d F Y') }}</p>
        @endif
        @if($wedding->venue)
        <p class="text-stone-400 text-sm mt-1">{{ $wedding->venue }}</p>
        @endif
        <div class="w-10 h-px bg-stone-300 mx-auto mt-8"></div>
    </div>
</section>
