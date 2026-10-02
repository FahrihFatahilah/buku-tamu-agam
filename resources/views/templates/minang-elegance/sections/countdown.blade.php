@php $countdownSection = $sections->firstWhere('section_key', 'countdown'); @endphp

<section id="countdown" class="section-bg relative py-20 px-6">

    @include('templates._section-bg', ['section' => $countdownSection, 'defaultBg' => '#F5F0E8'])

    <div class="section-content max-w-lg mx-auto text-center">
        <p class="text-[#B8960C] text-xs tracking-[0.3em] uppercase mb-8 reveal">Menuju Hari Bahagia</p>

        <div class="grid grid-cols-4 gap-4" id="countdown-grid">
            <div class="text-center reveal">
                <div class="text-3xl sm:text-4xl font-serif text-[#7C3238] flip-digit" id="cd-days">00</div>
                <div class="text-xs text-[#2C1810]/40 tracking-wider mt-1">Hari</div>
            </div>
            <div class="text-center reveal" style="transition-delay:80ms">
                <div class="text-3xl sm:text-4xl font-serif text-[#7C3238] flip-digit" id="cd-hours">00</div>
                <div class="text-xs text-[#2C1810]/40 tracking-wider mt-1">Jam</div>
            </div>
            <div class="text-center reveal" style="transition-delay:160ms">
                <div class="text-3xl sm:text-4xl font-serif text-[#7C3238] flip-digit" id="cd-mins">00</div>
                <div class="text-xs text-[#2C1810]/40 tracking-wider mt-1">Menit</div>
            </div>
            <div class="text-center reveal" style="transition-delay:240ms">
                <div class="text-3xl sm:text-4xl font-serif text-[#7C3238] flip-digit" id="cd-secs">00</div>
                <div class="text-xs text-[#2C1810]/40 tracking-wider mt-1">Detik</div>
            </div>
        </div>
        <div id="cd-passed" class="hidden col-span-4 text-center">
            <p class="font-serif text-[#7C3238] text-xl">Hari yang dinantikan telah tiba</p>
        </div>

        
    </div>
</section>

<script>
(function() {
    var target = new Date('{{ $wedding->date->format('Y-m-d') }}T00:00:00');
    var ids = { days:'cd-days', hours:'cd-hours', mins:'cd-mins', secs:'cd-secs' };
    var prev = {};

    function setFlip(id, val) {
        var el = document.getElementById(id);
        if (!el) return;
        var str = String(val).padStart(2,'0');
        if (prev[id] === str) return;
        prev[id] = str;
        el.classList.remove('flipping');
        void el.offsetWidth; /* reflow */
        el.textContent = str;
        el.classList.add('flipping');
    }

    function tick() {
        var diff = target - new Date();
        if (diff <= 0) {
            document.getElementById('countdown-grid').style.display = 'none';
            document.getElementById('cd-passed').classList.remove('hidden');
            return;
        }
        setFlip('cd-days',  Math.floor(diff / 86400000));
        setFlip('cd-hours', Math.floor((diff % 86400000) / 3600000));
        setFlip('cd-mins',  Math.floor((diff % 3600000) / 60000));
        setFlip('cd-secs',  Math.floor((diff % 60000) / 1000));
    }
    tick();
    setInterval(tick, 1000);
})();
</script>
