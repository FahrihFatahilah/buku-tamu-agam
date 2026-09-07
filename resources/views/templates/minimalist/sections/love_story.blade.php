<section id="love_story" class="py-20 px-6 bg-white">
    <div class="max-w-xl mx-auto">
        <p class="text-stone-400 text-xs tracking-[0.4em] uppercase mb-12 text-center reveal">Kisah Kami</p>
        @php $loveStorySection=$sections->firstWhere('section_key','love_story'); $stories=$loveStorySection?->settings['stories']??[['year'=>'2024','title'=>'Awal Pertemuan','description'=>'Berawal dari pertemuan sederhana yang tak terduga.'],['year'=>'2025','title'=>'Jatuh Cinta','description'=>'Perlahan kami menyadari bahwa kami saling melengkapi.'],['year'=>'2026','title'=>'Menuju Pelaminan','description'=>'Dengan penuh syukur, kami melangkah ke jenjang pernikahan.']]; @endphp
        <div class="space-y-8">
            @foreach($stories as $story)
            <div class="flex gap-6 reveal">
                <div class="shrink-0 w-16 text-right"><p class="text-stone-300 text-xs">{{ $story['year'] }}</p></div>
                <div class="w-px bg-stone-100 relative"><div class="absolute top-1 left-1/2 -translate-x-1/2 w-2 h-2 bg-stone-300 rounded-full"></div></div>
                <div class="flex-1 pb-8">
                    <h3 class="font-display text-lg text-stone-900 mb-1">{{ $story['title'] }}</h3>
                    <p class="text-stone-400 text-sm leading-relaxed">{{ $story['description'] }}</p>
                </div>
            </div>
            @endforeach
        </div>
    </div>
</section>
