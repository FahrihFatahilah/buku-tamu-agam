@if($guest)
<section id="rsvp" class="py-20 px-6 bg-[#F5F0E8]">
    <div class="max-w-md mx-auto">
        <div class="text-center mb-10">
            <p class="text-[#B8960C] text-xs tracking-[0.3em] uppercase mb-3">Konfirmasi Kehadiran</p>
            <h2 class="font-serif text-[#2C1810] text-2xl">RSVP</h2>
        </div>

        {{-- Form RSVP --}}
        <div id="rsvp-form-wrap">
            <form id="rsvp-form" class="space-y-5">
                @csrf
                <div>
                    <label class="block text-sm text-[#2C1810]/70 mb-2">Kehadiran</label>
                    <div class="space-y-2">
                        @foreach(['attending' => 'Hadir', 'not_attending' => 'Tidak Hadir', 'maybe' => 'Mungkin Hadir'] as $value => $label)
                        <label class="flex items-center gap-3 cursor-pointer">
                            <input type="radio" name="attendance_status" value="{{ $value }}"
                                {{ $guest->rsvp?->attendance_status === $value ? 'checked' : '' }}
                                class="border-[#7C3238] text-[#7C3238] focus:ring-[#7C3238]">
                            <span class="text-sm text-[#2C1810]">{{ $label }}</span>
                        </label>
                        @endforeach
                    </div>
                    <p id="err-attendance" class="mt-1 text-xs text-red-500 hidden"></p>
                </div>

                <div>
                    <label class="block text-sm text-[#2C1810]/70 mb-1.5">Jumlah Tamu (maks. {{ $guest->max_pax }})</label>
                    <input type="number" name="pax" min="1" max="{{ $guest->max_pax }}"
                        value="{{ $guest->rsvp?->pax ?? 1 }}"
                        class="w-full px-3 py-2 border border-[#2C1810]/20 bg-white text-sm text-[#2C1810] focus:outline-none focus:border-[#7C3238] transition-colors">
                    <p id="err-pax" class="mt-1 text-xs text-red-500 hidden"></p>
                </div>

                <div>
                    <label class="block text-sm text-[#2C1810]/70 mb-1.5">Pesan (opsional)</label>
                    <textarea name="note" rows="2"
                        class="w-full px-3 py-2 border border-[#2C1810]/20 bg-white text-sm text-[#2C1810] focus:outline-none focus:border-[#7C3238] transition-colors resize-none">{{ $guest->rsvp?->note }}</textarea>
                </div>

                <button type="submit" id="rsvp-btn"
                    class="w-full py-3 bg-[#7C3238] text-[#F5F0E8] text-sm tracking-wider hover:bg-[#6A2A2F] transition-colors focus:outline-none">
                    Kirim Konfirmasi
                </button>
            </form>
        </div>

        {{-- QR muncul setelah konfirmasi --}}
        <div id="rsvp-success" class="hidden text-center">
            <div class="mb-6">
                <svg class="w-12 h-12 mx-auto text-[#7C3238] mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
                <p class="font-serif text-[#2C1810] text-lg mb-1">Terima kasih, {{ $guest->name }}!</p>
                <p class="text-[#2C1810]/60 text-sm" id="rsvp-msg"></p>
            </div>

            <div id="qr-wrap" class="hidden">
                <div style="width:2.5rem;height:1px;background:rgba(44,24,16,0.2);margin:1.5rem auto;"></div>
                <p class="text-[#2C1810]/60 text-xs tracking-widest uppercase mb-4">QR Code Kehadiran</p>
                <div class="inline-block p-3 bg-white shadow-sm" id="qr-container">
                    <img id="qr-img" src="" alt="QR Code" class="w-48 h-48">
                </div>
                <p class="text-[#2C1810]/40 text-xs mt-3">Tunjukkan kepada panitia saat tiba di lokasi</p>
            </div>

            <button id="rsvp-edit" class="mt-6 text-xs text-[#7C3238] underline underline-offset-2">
                Ubah konfirmasi
            </button>
        </div>
    </div>
</section>

<script>
(function () {
    var form     = document.getElementById('rsvp-form');
    var formWrap = document.getElementById('rsvp-form-wrap');
    var success  = document.getElementById('rsvp-success');
    var btn      = document.getElementById('rsvp-btn');
    var msg      = document.getElementById('rsvp-msg');
    var qrWrap   = document.getElementById('qr-wrap');
    var qrImg    = document.getElementById('qr-img');
    var editBtn  = document.getElementById('rsvp-edit');

    @php
        $rsvpUrl = route('rsvp.store.short', [$wedding->short_id ?? $wedding->public_id, $wedding->slug, $guest->short_token ?? $guest->invitation_token]);
        $qrUrl = route('rsvp.qr', [
            'anyId' => $wedding->short_id ?? $wedding->public_id,
            'slug'  => $wedding->slug,
            'token' => $guest->short_token ?? $guest->invitation_token,
        ]);
    @endphp

    var ACTION  = @json($rsvpUrl);
    var QR_URL  = @json($qrUrl);
    var CSRF    = document.querySelector('#rsvp-form [name=_token]').value;

    // Jika sudah pernah RSVP, langsung tampilkan QR
    @if($guest->rsvp)
    showSuccess({{ $guest->rsvp->attendance_status === 'attending' ? 'true' : 'false' }}, false);
    @endif

    form.addEventListener('submit', function (e) {
        e.preventDefault();
        clearErrors();

        var data = new FormData(form);
        btn.disabled = true;
        btn.textContent = 'Mengirim...';

        fetch(ACTION, {
            method: 'POST',
            headers: { 'X-CSRF-TOKEN': CSRF, 'Accept': 'application/json' },
            body: data,
        })
        .then(function (r) { return r.json(); })
        .then(function (res) {
            if (res.success) {
                showSuccess(res.attending, true);
            } else if (res.errors) {
                Object.keys(res.errors).forEach(function (k) {
                    var el = document.getElementById('err-' + k);
                    if (el) { el.textContent = res.errors[k][0]; el.classList.remove('hidden'); }
                });
                btn.disabled = false;
                btn.textContent = 'Kirim Konfirmasi';
            }
        })
        .catch(function () {
            btn.disabled = false;
            btn.textContent = 'Kirim Konfirmasi';
        });
    });

    editBtn.addEventListener('click', function () {
        success.classList.add('hidden');
        formWrap.classList.remove('hidden');
        btn.disabled = false;
        btn.textContent = 'Kirim Konfirmasi';
    });

    function showSuccess(attending, loadQr) {
        formWrap.classList.add('hidden');
        success.classList.remove('hidden');
        msg.textContent = attending
            ? 'Konfirmasi kehadiran Anda telah kami terima.'
            : 'Kami mengerti, semoga bisa hadir di lain kesempatan.';

        if (attending) {
            qrWrap.classList.remove('hidden');
            if (loadQr) {
                qrImg.src = QR_URL + '?t=' + Date.now();
            } else {
                qrImg.src = QR_URL;
            }
        }
    }

    function clearErrors() {
        ['attendance', 'pax'].forEach(function (k) {
            var el = document.getElementById('err-' + k);
            if (el) { el.textContent = ''; el.classList.add('hidden'); }
        });
    }
})();
</script>
@endif
