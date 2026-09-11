@php $t = $text ?? []; @endphp
<section id="couple" class="py-20 px-6 tpl-surface">
    <div class="max-w-2xl mx-auto text-center">
        <p class="text-xs tracking-[0.3em] uppercase tpl-faint mb-3" data-edit="eyebrow">{{ $t['eyebrow'] ?? 'Mempelai' }}</p>
        <h2 class="tpl-display text-3xl tpl-ink mb-12" data-edit="heading">{{ $t['heading'] ?? 'Dengan Penuh Sukacita' }}</h2>

        <div class="space-y-12">
            <div>
                <h3 class="tpl-display text-2xl tpl-primary">{{ $wedding->bride_name }}</h3>
                @if($wedding->bride_nickname)
                <p class="text-sm tpl-muted mt-1">{{ $wedding->bride_nickname }}</p>
                @endif
                @if($wedding->bride_father || $wedding->bride_mother)
                <p class="text-sm tpl-muted mt-3"><span data-edit="bride_from">{{ $t['bride_from'] ?? 'Putri dari' }}</span>
                    {{ $wedding->bride_father ?: '—' }}@if($wedding->bride_mother) &amp; {{ $wedding->bride_mother }}@endif
                </p>
                @endif
            </div>

            <div class="flex items-center justify-center gap-4">
                <span class="w-12 h-px tpl-rule"></span>
                <span class="tpl-display text-xl italic tpl-accent">&amp;</span>
                <span class="w-12 h-px tpl-rule"></span>
            </div>

            <div>
                <h3 class="tpl-display text-2xl tpl-primary">{{ $wedding->groom_name }}</h3>
                @if($wedding->groom_nickname)
                <p class="text-sm tpl-muted mt-1">{{ $wedding->groom_nickname }}</p>
                @endif
                @if($wedding->groom_father || $wedding->groom_mother)
                <p class="text-sm tpl-muted mt-3"><span data-edit="groom_from">{{ $t['groom_from'] ?? 'Putra dari' }}</span>
                    {{ $wedding->groom_father ?: '—' }}@if($wedding->groom_mother) &amp; {{ $wedding->groom_mother }}@endif
                </p>
                @endif
            </div>
        </div>
    </div>
</section>
