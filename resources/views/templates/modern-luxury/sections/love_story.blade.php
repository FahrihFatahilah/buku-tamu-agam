<section id="love_story" class="py-20 px-6 bg-[#1a1a1a]">
    <div class="max-w-xl mx-auto">
        <p class="text-[#c9a96e] text-xs tracking-[0.4em] uppercase mb-12 text-center reveal">Our Story</p>
        @php
            $loveStorySection = $sections->firstWhere('section_key', 'love_story');
            $stories = $loveStorySection?->settings['stories'] ?? [['year'=>'2024','title'=>'Awal Pertemuan','description'=>'Berawal dari pertemuan sederhana yang tak terduga.'],['year'=>'2025','title'=>'Jatuh Cinta','description'=>'Perlahan kami menyadari bahwa kami saling melengkapi.'],['year'=>'2026','title'=>'Menuju Pelaminan','description'=>'Dengan penuh syukur, kami melangkah ke jenjang pernikahan.']];
        @endphp
        <div class="space-y-8">
            @foreach($stories as $story)
            <div class="flex gap-6 reveal">
                <div class="shrink-0 text-right w-20">
                    <p class="text-[#c9a96e] text-xs tracking-wider">{{ $story['year'] }}</p>
                </div>
                <div class="w-px bg-white/10 relative"><div class="absolute top-1 left-1/2 -translate-x-1/2 w-2 h-2 bg-[#c9a96e] rounded-full"></div></div>
                <div class="flex-1 pb-8">
                    <h3 class="font-display text-lg text-white mb-1">{{ $story['title'] }}</h3>
                    <p class="text-white/40 text-sm leading-relaxed">{{ $story['description'] }}</p>
                </div>
            </div>
            @endforeach
        </div>
    </div>
</section>
