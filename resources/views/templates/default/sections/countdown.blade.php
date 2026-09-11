@php
    $target = $wedding->date ? $wedding->date->copy()->setTime(8, 0) : null;
@endphp
@if($target)
<section class="py-20 px-6 bg-[#1a1a1a] text-white" x-data="{
    days:0, hours:0, minutes:0, seconds:0,
    init() {
        const target = new Date('{{ $target->toIso8601String() }}').getTime();
        const tick = () => {
            const diff = target - Date.now();
            if (diff <= 0) { this.days = this.hours = this.minutes = this.seconds = 0; return; }
            this.days = Math.floor(diff / 86400000);
            this.hours = Math.floor(diff % 86400000 / 3600000);
            this.minutes = Math.floor(diff % 3600000 / 60000);
            this.seconds = Math.floor(diff % 60000 / 1000);
        };
        tick(); setInterval(() => tick(), 1000);
    }
}">
    <div class="max-w-lg mx-auto text-center">
        <p class="text-xs tracking-[0.3em] uppercase text-white/50 mb-4">Menuju Hari Bahagia</p>
        <p class="font-display text-2xl mb-10">{{ $wedding->date->translatedFormat('d F Y') }}</p>
        <div class="grid grid-cols-4 gap-2 sm:gap-4">
            <template x-for="unit in [['Hari', days], ['Jam', hours], ['Menit', minutes], ['Detik', seconds]]" :key="unit[0]">
                <div class="border border-white/15 py-4">
                    <p class="font-display text-2xl sm:text-3xl" x-text="unit[1]"></p>
                    <p class="text-[10px] tracking-widest uppercase text-white/40 mt-1" x-text="unit[0]"></p>
                </div>
            </template>
        </div>
    </div>
</section>
@endif
