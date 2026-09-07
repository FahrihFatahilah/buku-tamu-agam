<section id="gift" class="py-20 px-6 bg-[#fdf8f0]">
    <div class="max-w-lg mx-auto">
        <p class="text-[#c9a84c] text-xs tracking-[0.4em] uppercase mb-3 text-center reveal">Hadiah Pernikahan</p>
        <h2 class="font-display text-2xl text-[#1a4a2e] text-center mb-3 reveal">Amplop Digital</h2>
        <p class="text-[#1a4a2e]/40 text-sm text-center mb-10 reveal">Doa dan kehadiran Anda adalah hadiah terbaik.</p>
        <div class="space-y-3">
            @foreach($giftMethods as $method)
            <div class="border border-[#c9a84c]/20 p-5 bg-white reveal">
                <div class="flex items-start justify-between gap-4">
                    <div class="flex-1">
                        <p class="font-medium text-[#1a4a2e] text-sm">{{ $method->label }}</p>
                        @if($method->type==='bank_transfer')
                        <p class="text-[#1a4a2e]/40 text-xs mt-1">{{ $method->bank_name }}</p>
                        <p class="font-mono text-[#1a4a2e] text-base mt-1">{{ $method->account_number }}</p>
                        <p class="text-[#1a4a2e]/40 text-xs mt-0.5">a.n. {{ $method->account_holder }}</p>
                        @elseif($method->type==='qris' && $method->image)
                        <div class="mt-3"><img src="{{ Storage::url($method->image) }}" alt="QRIS" class="w-32 h-32 object-contain"></div>
                        @endif
                        @if($method->description)<p class="text-[#1a4a2e]/30 text-xs mt-2">{{ $method->description }}</p>@endif
                    </div>
                    @if($method->type==='bank_transfer')
                    <button onclick="navigator.clipboard.writeText('{{ $method->account_number }}')" class="shrink-0 px-3 py-1.5 border border-[#c9a84c]/30 text-[#c9a84c] text-xs hover:bg-[#c9a84c]/5 transition-colors">Salin</button>
                    @endif
                </div>
            </div>
            @endforeach
        </div>
    </div>
</section>
