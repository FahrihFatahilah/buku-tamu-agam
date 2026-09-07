<section id="love_story" class="py-20 px-6 bg-[#f5ede0]">
    <div class="max-w-xl mx-auto">
        <div class="text-center mb-12">
            <p class="text-[#c9a84c] text-xs tracking-[0.3em] uppercase mb-3 reveal">Kisah Kami</p>
            <h2 class="font-serif text-3xl text-[#3d1a1a] reveal">Perjalanan Cinta</h2>
        </div>

        @php
            $loveStorySection = $sections->firstWhere('section_key', 'love_story');
            $stories = $loveStorySection?->settings['stories'] ?? [
                [
                    'year' => '2024 December',
                    'title' => 'Awal Pertemuan',
                    'description' => 'Berawal dari pertemuan sederhana yang mempertemukan dua hati. Awalnya kami hanya saling mengenal dan bertukar cerita, tanpa pernah menyangka bahwa perkenalan ini akan menjadi awal dari perjalanan panjang bersama.'
                ],
                [
                    'year' => '2025 February 2',
                    'title' => 'Memilih untuk Bersama',
                    'description' => 'Pada hari ini, kami memutuskan untuk saling menyatakan perasaan dan memulai hubungan. Kami belajar untuk menerima satu sama lain, dengan segala kelebihan dan kekurangan, serta tumbuh bersama dalam setiap prosesnya.'
                ],
                [
                    'year' => '2025 October',
                    'title' => 'Menuju Jenjang yang Lebih Serius',
                    'description' => 'Setelah melewati banyak cerita, tawa, dan berbagai proses bersama, kami semakin yakin dengan pilihan ini. Kami pun sepakat untuk melanjutkan hubungan ke jenjang yang lebih serius dan mulai menata langkah untuk masa depan bersama.'
                ],
                [
                    'year' => '2026 August 2',
                    'title' => 'Pertemuan Dua Keluarga',
                    'description' => 'Menjadi salah satu momen yang begitu berarti bagi kami. Pada hari ini, dua keluarga dipertemukan untuk saling mengenal dan bersama-sama memberikan doa serta restu untuk langkah kami selanjutnya.'
                ],
            ];
        @endphp

        <div class="relative">
            <div class="absolute left-1/2 top-0 bottom-0 w-px bg-[#c9a84c]/20 -translate-x-1/2"></div>

            @foreach($stories as $i => $story)
            <div class="relative flex items-start gap-6 mb-10 reveal {{ $i % 2 === 0 ? 'flex-row' : 'flex-row-reverse' }}">
                <div class="flex-1 {{ $i % 2 === 0 ? 'text-right' : 'text-left' }}">
                    <p class="text-[#c9a84c] text-xs tracking-widest mb-1">{{ $story['year'] }}</p>
                    <h3 class="font-serif text-lg text-[#3d1a1a] mb-1">{{ $story['title'] }}</h3>
                    <p class="text-[#6b4c3b]/70 text-sm leading-relaxed">{{ $story['description'] }}</p>
                </div>
                <div class="w-3 h-3 rounded-full bg-[#c9a84c] border-2 border-[#f5ede0] shrink-0 mt-1.5 relative z-10"></div>
                <div class="flex-1"></div>
            </div>
            @endforeach
        </div>
    </div>
</section>
