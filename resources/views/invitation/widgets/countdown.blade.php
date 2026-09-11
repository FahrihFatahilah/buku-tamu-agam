@php
    $p = $node['props'] ?? [];
    $target = $wedding->date ? $wedding->date->copy()->setTime(8, 0) : null;
    $showSeconds = $p['showSeconds'] ?? true;
@endphp
@if($target)
<div class="n-inner"
    data-countdown="{{ $target->toIso8601String() }}"
    x-data="{
        days:0, hours:0, minutes:0, seconds:0,
        init() {
            const target = new Date(this.$el.dataset.countdown).getTime();
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
    @if(!empty($p['label']))
    <p class="text-xs tracking-[0.3em] uppercase opacity-50 mb-4" data-edit-prop="label">{{ $p['label'] }}</p>
    @endif

    <div class="grid grid-cols-4 gap-2 sm:gap-4">
        <template x-for="unit in [['Hari', days], ['Jam', hours], ['Menit', minutes]{{ $showSeconds ? ", ['Detik', seconds]" : '' }}]" :key="unit[0]">
            <div class="py-4" style="border: 1px solid currentColor; border-color: color-mix(in srgb, currentColor 20%, transparent);">
                <p class="n-display text-2xl sm:text-3xl" x-text="unit[1]"></p>
                <p class="text-[10px] tracking-widest uppercase opacity-50 mt-1" x-text="unit[0]"></p>
            </div>
        </template>
    </div>
</div>
@endif
