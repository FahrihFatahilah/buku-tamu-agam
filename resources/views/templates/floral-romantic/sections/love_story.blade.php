<section id="love_story" class="py-20 px-6 bg-white">
    <div class="max-w-xl mx-auto">
        <p class="text-[#b5606a]/60 text-xs tracking-[0.4em] uppercase mb-12 text-center reveal">Kisah Cinta Kami</p>
        @php $loveStorySection=$sections->firstWhere('section_key','love_story'); $stories=$loveStorySection?->settings['stories']??[['year'=>'2024','title'=>'Awal Pertemuan','description'=>'Berawal dari pertemuan sederhana yang tak terduga.'],['year'=>'2025','title'=>'Jatuh Cinta','description'=>'Perlahan kami menyadari bahwa kami saling melengkapi.'],['year'=>'2026','title'=>'Menuju Pelaminan','description'=>'Dengan penuh syukur, kami melangkah ke jenjang pernikahan.']]; @endphp
        <div class="relative">
            <div class="absolute left-1/2 top-0 bottom-0 w-px bg-[#b5606a]/15 -translate-x-1/2"></div>
            @foreach($stories as $i=>$story)
            <div class="relative flex items-start gap-6 mb-10 reveal {{ $i%2===0?'flex-row':'flex-row-reverse' }}">
                <div class="flex-1 {{ $i%2===0?'text-right':'text-left' }}">
                    <p class="text-[#b5606a] text-xs tracking-widest mb-1">{{ $story['year'] }}</p>
                    <h3 class="font-display text-lg text-[#2a1a1a] italic mb-1">{{ $story['title'] }}</h3>
                    <p class="text-[#2a1a1a]/50 text-sm leading-relaxed">{{ $story['description'] }}</p>
                </div>
                <div class="w-3 h-3 rounded-full bg-[#b5606a] border-2 border-[#f9f0f0] shrink-0 mt-1.5 relative z-10"></div>
                <div class="flex-1"></div>
            </div>
            @endforeach
        </div>
    </div>
</section>
