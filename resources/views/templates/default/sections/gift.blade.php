<section class="py-20 px-6 bg-stone-50">
    <div class="max-w-lg mx-auto">
        <div class="text-center mb-12">
            <p class="text-xs tracking-[0.3em] uppercase text-stone-400 mb-3">Hadiah Pernikahan</p>
            <h2 class="font-display text-3xl text-stone-800">Amplop Digital</h2>
            <p class="text-sm text-stone-500 mt-3">Doa dan kehadiran Anda adalah hadiah terbaik bagi kami.</p>
        </div>

        <div class="space-y-3">
            @foreach($giftMethods as $method)
            <div class="border border-stone-200 p-5 bg-white">
                <div class="flex items-start justify-between gap-4">
                    <div class="flex-1 min-w-0">
                        <p class="font-medium text-stone-800 text-sm">{{ $method->label }}</p>

                        @if($method->type === 'bank_transfer')
                        @if($method->bank_name)
                        <p class="text-stone-400 text-xs mt-1">{{ $method->bank_name }}</p>
                        @endif
                        <p class="font-mono text-stone-800 text-base mt-1">{{ $method->account_number }}</p>
                        @if($method->account_holder)
                        <p class="text-stone-400 text-xs mt-0.5">a.n. {{ $method->account_holder }}</p>
                        @endif
                        @elseif($method->type === 'qris' && $method->image)
                        <div class="mt-3">
                            <img src="{{ Storage::url($method->image) }}" alt="QRIS {{ $method->merchant_name }}"
                                class="w-32 h-32 object-contain border border-stone-100">
                        </div>
                        @elseif($method->merchant_name)
                        <p class="text-stone-500 text-sm mt-1">{{ $method->merchant_name }}</p>
                        @endif

                        @if($method->description)
                        <p class="text-stone-400 text-xs mt-2">{{ $method->description }}</p>
                        @endif
                    </div>

                    @if($method->type === 'bank_transfer' && $method->account_number)
                    <button type="button"
                        onclick="navigator.clipboard.writeText('{{ $method->account_number }}')"
                        class="shrink-0 px-3 py-1.5 border border-stone-200 text-stone-500 text-xs hover:border-stone-400 transition-colors">
                        Salin
                    </button>
                    @endif
                </div>
            </div>
            @endforeach
        </div>
    </div>
</section>
