<section id="countdown" class="py-20 px-6 bg-[#fdf5e4]" x-data="countdown('{{ $wedding->date->format('Y-m-d') }}')">
    <div class="max-w-lg mx-auto text-center">
        <p class="text-[#c9a84c] text-xs tracking-[0.4em] uppercase mb-10 reveal">Menuju Hari Bahagia</p>
        <div class="grid grid-cols-4 gap-4">
            <template x-if="!passed"><template x-for="unit in units" :key="unit.label"><div class="reveal"><div class="text-4xl font-display text-[#4a2c0a]" x-text="unit.value"></div><div class="text-xs text-[#4a2c0a]/40 tracking-widest mt-1" x-text="unit.label"></div></div></template></template>
            <template x-if="passed"><div class="col-span-4"><p class="font-display text-2xl text-[#4a2c0a]">Hari yang dinantikan telah tiba</p></div></template>
        </div>
        <p class="mt-8 text-[#4a2c0a]/40 text-sm reveal">{{ $wedding->date->translatedFormat('l, d F Y') }}</p>
    </div>
</section>
<script>
function countdown(dateStr){return{units:[],passed:false,init(){this.update();setInterval(()=>this.update(),1000);},update(){const diff=new Date(dateStr+'T00:00:00')-new Date();if(diff<=0){this.passed=true;return;}const d=Math.floor(diff/86400000),h=Math.floor((diff%86400000)/3600000),m=Math.floor((diff%3600000)/60000),s=Math.floor((diff%60000)/1000);this.units=[{value:String(d).padStart(2,'0'),label:'Hari'},{value:String(h).padStart(2,'0'),label:'Jam'},{value:String(m).padStart(2,'0'),label:'Menit'},{value:String(s).padStart(2,'0'),label:'Detik'}];}};}
</script>
