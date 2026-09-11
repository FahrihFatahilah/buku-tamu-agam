<section class="py-20 px-6 bg-white">
    <div class="max-w-lg mx-auto">
        <div class="text-center mb-12">
            <p class="text-xs tracking-[0.3em] uppercase text-stone-400 mb-3">Rangkaian Acara</p>
            <h2 class="font-display text-3xl text-stone-800">Jadwal Acara</h2>
        </div>
        <div class="space-y-4">
            @foreach($events as $event)
            <div class="border border-stone-200 p-6">
                <p class="font-display text-xl text-stone-800">{{ $event->name }}</p>
                @if($event->starts_at)
                <p class="text-sm text-stone-500 mt-2">
                    {{ $event->starts_at->translatedFormat('l, d F Y') }}
                    · {{ $event->starts_at->format('H:i') }}@if($event->ends_at)–{{ $event->ends_at->format('H:i') }} WIB@endif
                </p>
                @endif
                @if($event->venue)
                <p class="text-sm text-stone-600 mt-2">{{ $event->venue }}</p>
                @endif
                @if($event->address)
                <p class="text-xs text-stone-400 mt-1">{{ $event->address }}</p>
                @endif
                @if($event->dress_code)
                <p class="text-xs text-stone-400 mt-2">Dress code: {{ $event->dress_code }}</p>
                @endif
            </div>
            @endforeach
        </div>
    </div>
</section>
