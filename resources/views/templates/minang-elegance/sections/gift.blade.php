<section id="gift" class="py-20 px-6 bg-[#F5F0E8]">
    <div class="max-w-lg mx-auto">
        <div class="text-center mb-10">
            <p class="text-[#B8960C] text-xs tracking-[0.3em] uppercase mb-3">Hadiah Pernikahan</p>
            <h2 class="font-serif text-[#2C1810] text-2xl">Amplop Digital</h2>
            <p class="text-[#2C1810]/50 text-sm mt-3">Doa dan kehadiran Anda adalah hadiah terbaik bagi kami.</p>
        </div>

        <div class="space-y-3">
            @foreach($giftMethods as $method)
            <div class="border border-[#2C1810]/10 p-5 bg-white">
                <div class="flex items-start justify-between gap-4">
                    <div class="flex-1">
                        <p class="font-medium text-[#2C1810] text-sm">{{ $method->label }}</p>

                        @if($method->type === 'bank_transfer')
                        <p class="text-[#2C1810]/50 text-xs mt-1">{{ $method->bank_name }}</p>
                        <p class="font-mono text-[#7C3238] text-base mt-1">{{ $method->account_number }}</p>
                        <p class="text-[#2C1810]/50 text-xs mt-0.5">a.n. {{ $method->account_holder }}</p>
                        @elseif($method->type === 'qris' && $method->image)
                        <div class="mt-3">
                            <img src="{{ Storage::url($method->image) }}" alt="QRIS {{ $method->merchant_name }}" class="w-32 h-32 object-contain">
                        </div>
                        @endif

                        @if($method->description)
                        <p class="text-[#2C1810]/40 text-xs mt-2">{{ $method->description }}</p>
                        @endif
                    </div>

                    @if($method->type === 'bank_transfer')
                    <button
                        onclick="navigator.clipboard.writeText('{{ $method->account_number }}')"
                        class="shrink-0 px-3 py-1.5 border border-[#2C1810]/15 text-[#2C1810]/50 text-xs hover:border-[#7C3238]/30 hover:text-[#7C3238] transition-colors">
                        Salin
                    </button>
                    @endif
                </div>
            </div>
            @endforeach
        </div>
    </div>
</section>
