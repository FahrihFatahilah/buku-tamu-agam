<section id="gift" class="py-20 px-6 bg-white">
    <div class="max-w-lg mx-auto">
        <p class="text-[#b5606a]/60 text-xs tracking-[0.4em] uppercase mb-3 text-center reveal">Hadiah Pernikahan</p>
        <h2 class="font-display text-2xl text-[#2a1a1a] italic text-center mb-3 reveal">Amplop Digital</h2>
        <p class="text-[#2a1a1a]/40 text-sm text-center mb-10 reveal">Doa dan kehadiran Anda adalah hadiah terbaik.</p>
        <div class="space-y-3">
            @foreach($giftMethods as $method)
            <div class="border border-[#b5606a]/10 p-5 bg-[#f9f0f0] reveal">
                <div class="flex items-start justify-between gap-4">
                    <div class="flex-1">
                        <p class="font-medium text-[#2a1a1a] text-sm">{{ $method->label }}</p>
                        @if($method->type==='bank_transfer')
                        <p class="text-[#2a1a1a]/40 text-xs mt-1">{{ $method->bank_name }}</p>
                        <p class="font-mono text-[#b5606a] text-base mt-1">{{ $method->account_number }}</p>
                        <p class="text-[#2a1a1a]/40 text-xs mt-0.5">a.n. {{ $method->account_holder }}</p>
                        @elseif($method->type==='qris' && $method->image)
                        <div class="mt-3"><img src="{{ Storage::url($method->image) }}" alt="QRIS" class="w-32 h-32 object-contain"></div>
                        @endif
                        @if($method->description)<p class="text-[#2a1a1a]/30 text-xs mt-2">{{ $method->description }}</p>@endif
                    </div>
                    @if($method->type==='bank_transfer')
                    <button onclick="navigator.clipboard.writeText('{{ $method->account_number }}')" class="shrink-0 px-3 py-1.5 border border-[#b5606a]/20 text-[#b5606a]/60 text-xs hover:border-[#b5606a]/50 transition-colors">Salin</button>
                    @endif
                </div>
            </div>
            @endforeach
        </div>
    </div>
</section>
