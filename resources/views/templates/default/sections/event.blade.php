<section class="py-20 px-6 tpl-surface">
    <div class="max-w-lg mx-auto">
        <div class="text-center mb-12">
            <p class="text-xs tracking-[0.3em] uppercase tpl-faint mb-3">Rangkaian Acara</p>
            <h2 class="tpl-display text-3xl tpl-ink">Jadwal Acara</h2>
        </div>
        <div class="space-y-4">
            @foreach($events as $event)
            <div class="border tpl-hairline p-6 tpl-panel">
                <p class="tpl-display text-xl tpl-primary">{{ $event->name }}</p>
                @if($event->starts_at)
                <p class="text-sm tpl-muted mt-2">
                    {{ $event->starts_at->translatedFormat('l, d F Y') }}
                    · {{ $event->starts_at->format('H:i') }}@if($event->ends_at)–{{ $event->ends_at->format('H:i') }} WIB@endif
                </p>
                @endif
                @if($event->venue)
                <p class="text-sm tpl-ink mt-2">{{ $event->venue }}</p>
                @endif
                @if($event->address)
                <p class="text-xs tpl-faint mt-1">{{ $event->address }}</p>
                @endif
                @if($event->dress_code)
                <p class="text-xs tpl-faint mt-2">Dress code: {{ $event->dress_code }}</p>
                @endif
            </div>
            @endforeach
        </div>
    </div>
</section>
