<section id="rsvp" class="py-20 px-6 bg-[#1a1a1a]">
    <div class="max-w-md mx-auto">
        <p class="text-[#c9a96e] text-xs tracking-[0.4em] uppercase mb-3 text-center reveal">Konfirmasi Kehadiran</p>
        <h2 class="font-display text-2xl text-white text-center mb-10 reveal">RSVP</h2>
        @if(session('success'))<div class="mb-6 px-4 py-3 bg-green-900/30 border border-green-700/30 text-green-400 text-sm text-center">{{ session('success') }}</div>@endif
        @php $rsvp = $guest->rsvp; @endphp
        <form method="POST" action="{{ route('rsvp.store.short',[$wedding->short_id??$wedding->public_id,$wedding->slug,$guest->short_token??$guest->invitation_token]) }}" class="space-y-5 reveal">
            @csrf
            <div>
                <label class="block text-xs text-white/40 tracking-wider mb-3 uppercase">Kehadiran</label>
                <div class="space-y-2">
                    @foreach(['attending'=>'Hadir','not_attending'=>'Tidak Hadir','maybe'=>'Mungkin Hadir'] as $value=>$label)
                    <label class="flex items-center gap-3 cursor-pointer">
                        <input type="radio" name="attendance_status" value="{{ $value }}" {{ old('attendance_status',$rsvp?->attendance_status)===$value?'checked':'' }} class="text-[#c9a96e]">
                        <span class="text-sm text-white/70">{{ $label }}</span>
                    </label>
                    @endforeach
                </div>
            </div>
            <div>
                <label class="block text-xs text-white/40 tracking-wider mb-2 uppercase">Jumlah Tamu (maks. {{ $guest->max_pax }})</label>
                <input type="number" name="pax" min="1" max="{{ $guest->max_pax }}" value="{{ old('pax',$rsvp?->pax??1) }}" class="w-full px-4 py-3 bg-white/5 border border-white/10 text-white text-sm focus:outline-none focus:border-[#c9a96e]/50 transition-colors">
            </div>
            <div>
                <label class="block text-xs text-white/40 tracking-wider mb-2 uppercase">Pesan</label>
                <textarea name="note" rows="2" class="w-full px-4 py-3 bg-white/5 border border-white/10 text-white text-sm focus:outline-none focus:border-[#c9a96e]/50 transition-colors resize-none">{{ old('note',$rsvp?->note) }}</textarea>
            </div>
            <button type="submit" class="w-full py-3 border border-[#c9a96e]/50 text-[#c9a96e] text-xs tracking-[0.2em] uppercase hover:bg-[#c9a96e]/10 transition-colors">Kirim Konfirmasi</button>
        </form>
    </div>
</section>
