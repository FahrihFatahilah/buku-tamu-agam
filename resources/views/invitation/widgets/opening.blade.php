@php $p = $node['props'] ?? []; @endphp
<div class="n-inner"
    x-data="{ opened: false }"
    x-show="!opened"
    x-cloak
    x-transition:leave="transition duration-700 ease-in"
    x-transition:leave-start="opacity-100"
    x-transition:leave-end="opacity-0">
    <div class="flex flex-col items-center justify-center text-center min-h-screen px-8"
        style="background: var(--n-primary, #1a1a1a); color: #fff;">

        @if(!empty($p['eyebrow']))
        <p class="text-xs tracking-[0.4em] uppercase mb-6 opacity-60" data-edit-prop="eyebrow">{{ $p['eyebrow'] }}</p>
        @endif

        <h1 class="n-display text-4xl sm:text-5xl font-normal mb-2">{{ $wedding->bride_name }}</h1>
        <p class="text-xl n-display italic mb-2 opacity-70">&amp;</p>
        <h1 class="n-display text-4xl sm:text-5xl font-normal mb-8">{{ $wedding->groom_name }}</h1>

        @if(($p['showGuestName'] ?? true) && $guest)
        <p class="text-sm opacity-60 mb-6" data-edit-prop="guestPrefix">
            Kepada: <span data-edit-prop="guestName">{{ $guest->name }}</span>
        </p>
        @endif

        <button type="button" @click="opened = true"
            class="px-8 py-3 text-xs tracking-[0.2em] uppercase"
            style="border: 1px solid color-mix(in srgb, #fff 35%, transparent); color: #fff;"
            data-edit-prop="cta">{{ $p['cta'] ?? 'Buka Undangan' }}</button>
    </div>
</div>
