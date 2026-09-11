@php
    $p = $node['props'] ?? [];
    $showNick = $p['showNickname'] ?? true;
    $showParents = $p['showParents'] ?? false;
@endphp
<div class="n-inner text-center">
    @if(!empty($p['eyebrow']))
    <p class="text-xs tracking-[0.3em] uppercase mb-3 opacity-60" data-edit-prop="eyebrow">{{ $p['eyebrow'] }}</p>
    @endif

    <div class="space-y-8">
        <div>
            <h3 class="n-display text-2xl" data-edit-prop="brideName">{{ $wedding->bride_name }}</h3>
            @if($showNick && $wedding->bride_nickname)
            <p class="text-sm opacity-60 mt-1">{{ $wedding->bride_nickname }}</p>
            @endif
            @if($showParents && ($wedding->bride_father || $wedding->bride_mother))
            <p class="text-sm opacity-70 mt-3">
                Putri dari {{ $wedding->bride_father ?: '—' }}@if($wedding->bride_mother) &amp; {{ $wedding->bride_mother }}@endif
            </p>
            @endif
        </div>

        <div class="flex items-center justify-center gap-4" aria-hidden="true">
            <span class="w-12 h-px" style="background: currentColor; opacity: .25;"></span>
            <span class="n-display text-xl italic">&#38;</span>
            <span class="w-12 h-px" style="background: currentColor; opacity: .25;"></span>
        </div>

        <div>
            <h3 class="n-display text-2xl" data-edit-prop="groomName">{{ $wedding->groom_name }}</h3>
            @if($showNick && $wedding->groom_nickname)
            <p class="text-sm opacity-60 mt-1">{{ $wedding->groom_nickname }}</p>
            @endif
            @if($showParents && ($wedding->groom_father || $wedding->groom_mother))
            <p class="text-sm opacity-70 mt-3">
                Putra dari {{ $wedding->groom_father ?: '—' }}@if($wedding->groom_mother) &amp; {{ $wedding->groom_mother }}@endif
            </p>
            @endif
        </div>
    </div>
</div>
