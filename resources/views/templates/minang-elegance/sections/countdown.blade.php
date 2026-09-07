@php $countdownSection = $sections->firstWhere('section_key', 'countdown'); @endphp

<section id="countdown" class="section-bg relative py-20 px-6"
    x-data="countdown('{{ $wedding->date->format('Y-m-d') }}')">

    @include('templates._section-bg', ['section' => $countdownSection, 'defaultBg' => '#F5F0E8'])

    <div class="section-content max-w-lg mx-auto text-center">
        <p class="text-[#B8960C] text-xs tracking-[0.3em] uppercase mb-8 reveal">Menuju Hari Bahagia</p>

        <div class="grid grid-cols-4 gap-4 stagger-children">
            <template x-if="!passed">
                <template x-for="unit in units" :key="unit.label">
                    <div class="text-center">
                        <div class="text-3xl sm:text-4xl font-serif text-[#7C3238]" x-text="unit.value"></div>
                        <div class="text-xs text-[#2C1810]/40 tracking-wider mt-1" x-text="unit.label"></div>
                    </div>
                </template>
            </template>
            <template x-if="passed">
                <div class="col-span-4">
                    <p class="font-serif text-[#7C3238] text-xl">Hari yang dinantikan telah tiba</p>
                </div>
            </template>
        </div>

        
    </div>
</section>

<script>
function countdown(dateStr) {
    return {
        units: [], passed: false,
        init() {
            this.update();
            setInterval(() => this.update(), 1000);
        },
        update() {
            const diff = new Date(dateStr + 'T00:00:00') - new Date();
            if (diff <= 0) { this.passed = true; return; }
            const d = Math.floor(diff / 86400000);
            const h = Math.floor((diff % 86400000) / 3600000);
            const m = Math.floor((diff % 3600000) / 60000);
            const s = Math.floor((diff % 60000) / 1000);
            this.units = [
                { value: String(d).padStart(2,'0'), label: 'Hari' },
                { value: String(h).padStart(2,'0'), label: 'Jam' },
                { value: String(m).padStart(2,'0'), label: 'Menit' },
                { value: String(s).padStart(2,'0'), label: 'Detik' },
            ];
        }
    };
}
</script>
