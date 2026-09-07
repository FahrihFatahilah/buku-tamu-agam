<section id="rsvp" class="py-20 px-6 bg-[#F5F0E8]">
    <div class="max-w-md mx-auto">
        <div class="text-center mb-10">
            <p class="text-[#B8960C] text-xs tracking-[0.3em] uppercase mb-3">Konfirmasi Kehadiran</p>
            <h2 class="font-serif text-[#2C1810] text-2xl">RSVP</h2>
        </div>

        @if(session('success'))
        <div class="mb-6 px-4 py-3 bg-green-50 border border-green-200 text-green-700 text-sm text-center">
            {{ session('success') }}
        </div>
        @endif

        @php $rsvp = $guest->rsvp; @endphp

        <form method="POST" action="{{ route('rsvp.store', [$wedding->public_id, $wedding->slug, $guest->invitation_token]) }}" class="space-y-5">
            @csrf

            <div>
                <label class="block text-sm text-[#2C1810]/70 mb-2">Kehadiran</label>
                <div class="space-y-2">
                    @foreach(['attending' => 'Hadir', 'not_attending' => 'Tidak Hadir', 'maybe' => 'Mungkin Hadir'] as $value => $label)
                    <label class="flex items-center gap-3 cursor-pointer">
                        <input type="radio" name="attendance_status" value="{{ $value }}"
                            {{ old('attendance_status', $rsvp?->attendance_status) === $value ? 'checked' : '' }}
                            class="border-[#7C3238] text-[#7C3238] focus:ring-[#7C3238]">
                        <span class="text-sm text-[#2C1810]">{{ $label }}</span>
                    </label>
                    @endforeach
                </div>
                @error('attendance_status')<p class="mt-1 text-xs text-red-500">{{ $message }}</p>@enderror
            </div>

            <div>
                <label class="block text-sm text-[#2C1810]/70 mb-1.5">Jumlah Tamu (maks. {{ $guest->max_pax }})</label>
                <input type="number" name="pax" min="1" max="{{ $guest->max_pax }}"
                    value="{{ old('pax', $rsvp?->pax ?? 1) }}"
                    class="w-full px-3 py-2 border border-[#2C1810]/20 bg-white text-sm text-[#2C1810] focus:outline-none focus:border-[#7C3238] transition-colors">
                @error('pax')<p class="mt-1 text-xs text-red-500">{{ $message }}</p>@enderror
            </div>

            <div>
                <label class="block text-sm text-[#2C1810]/70 mb-1.5">Pesan (opsional)</label>
                <textarea name="note" rows="2"
                    class="w-full px-3 py-2 border border-[#2C1810]/20 bg-white text-sm text-[#2C1810] focus:outline-none focus:border-[#7C3238] transition-colors resize-none">{{ old('note', $rsvp?->note) }}</textarea>
            </div>

            <button type="submit"
                class="w-full py-3 bg-[#7C3238] text-[#F5F0E8] text-sm tracking-wider hover:bg-[#6A2A2F] transition-colors focus:outline-none focus:ring-2 focus:ring-[#7C3238] focus:ring-offset-2">
                Kirim Konfirmasi
            </button>
        </form>
    </div>
</section>
