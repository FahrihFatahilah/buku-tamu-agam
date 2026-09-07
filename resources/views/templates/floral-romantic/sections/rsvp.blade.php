<section id="rsvp" class="py-20 px-6 bg-white">
    <div class="max-w-md mx-auto">
        <p class="text-[#b5606a]/60 text-xs tracking-[0.4em] uppercase mb-3 text-center reveal">Konfirmasi Kehadiran</p>
        <h2 class="font-display text-2xl text-[#2a1a1a] italic text-center mb-10 reveal">RSVP</h2>
        @if(session('success'))<div class="mb-6 px-4 py-3 bg-green-50 border border-green-200 text-green-700 text-sm text-center">{{ session('success') }}</div>@endif
        @php $rsvp=$guest->rsvp; @endphp
        <form method="POST" action="{{ route('rsvp.store',[$wedding->public_id,$wedding->slug,$guest->invitation_token]) }}" class="space-y-5 reveal">
            @csrf
            <div>
                <label class="block text-xs text-[#2a1a1a]/40 tracking-wider mb-3 uppercase">Kehadiran</label>
                <div class="space-y-2">
                    @foreach(['attending'=>'Hadir','not_attending'=>'Tidak Hadir','maybe'=>'Mungkin Hadir'] as $value=>$label)
                    <label class="flex items-center gap-3 cursor-pointer"><input type="radio" name="attendance_status" value="{{ $value }}" {{ old('attendance_status',$rsvp?->attendance_status)===$value?'checked':'' }} class="text-[#b5606a]"><span class="text-sm text-[#2a1a1a]/70">{{ $label }}</span></label>
                    @endforeach
                </div>
            </div>
            <div>
                <label class="block text-xs text-[#2a1a1a]/40 tracking-wider mb-2 uppercase">Jumlah Tamu (maks. {{ $guest->max_pax }})</label>
                <input type="number" name="pax" min="1" max="{{ $guest->max_pax }}" value="{{ old('pax',$rsvp?->pax??1) }}" class="w-full px-4 py-3 border border-[#b5606a]/20 text-sm focus:outline-none focus:border-[#b5606a]/50 transition-colors">
            </div>
            <div>
                <label class="block text-xs text-[#2a1a1a]/40 tracking-wider mb-2 uppercase">Pesan</label>
                <textarea name="note" rows="2" class="w-full px-4 py-3 border border-[#b5606a]/20 text-sm focus:outline-none focus:border-[#b5606a]/50 transition-colors resize-none">{{ old('note',$rsvp?->note) }}</textarea>
            </div>
            <button type="submit" class="w-full py-3 bg-[#b5606a] text-white text-xs tracking-[0.2em] uppercase hover:bg-[#9a4f58] transition-colors">Kirim Konfirmasi</button>
        </form>
    </div>
</section>
