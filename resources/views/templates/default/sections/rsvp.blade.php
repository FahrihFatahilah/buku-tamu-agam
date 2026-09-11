@if($guest)
<section class="py-20 px-6 bg-white">
    <div class="max-w-md mx-auto">
        <div class="text-center mb-10">
            <p class="text-xs tracking-[0.3em] uppercase text-stone-400 mb-3">Konfirmasi Kehadiran</p>
            <h2 class="font-display text-3xl text-stone-800">RSVP</h2>
        </div>

        <div id="rsvp-form-wrap">
            <form id="rsvp-form" class="space-y-5">
                @csrf
                <div>
                    <label class="block text-sm text-stone-600 mb-2">Kehadiran</label>
                    <div class="space-y-2">
                        @foreach(['attending' => 'Hadir', 'not_attending' => 'Tidak Hadir', 'maybe' => 'Mungkin Hadir'] as $value => $label)
                        <label class="flex items-center gap-3 cursor-pointer">
                            <input type="radio" name="attendance_status" value="{{ $value }}"
                                {{ $guest->rsvp?->attendance_status === $value ? 'checked' : '' }}
                                class="border-stone-300 text-stone-800 focus:ring-stone-500">
                            <span class="text-sm text-stone-700">{{ $label }}</span>
                        </label>
                        @endforeach
                    </div>
                    <p id="err-attendance" class="mt-1 text-xs text-red-500 hidden"></p>
                </div>

                <div>
                    <label class="block text-sm text-stone-600 mb-1.5">Jumlah Tamu (maks. {{ $guest->max_pax }})</label>
                    <input type="number" name="pax" min="1" max="{{ $guest->max_pax }}"
                        value="{{ $guest->rsvp?->pax ?? 1 }}"
                        class="w-full px-3 py-2 border border-stone-200 text-sm focus:outline-none focus:border-stone-400">
                    <p id="err-pax" class="mt-1 text-xs text-red-500 hidden"></p>
                </div>

                <div>
                    <label class="block text-sm text-stone-600 mb-1.5">Pesan (opsional)</label>
                    <textarea name="note" rows="2"
                        class="w-full px-3 py-2 border border-stone-200 text-sm focus:outline-none focus:border-stone-400 resize-none">{{ $guest->rsvp?->note }}</textarea>
                </div>

                <button type="submit" id="rsvp-btn"
                    class="w-full py-3 bg-stone-800 text-white text-sm tracking-wider hover:bg-stone-700 transition-colors">
                    Kirim Konfirmasi
                </button>
            </form>
        </div>

        <div id="rsvp-success" class="hidden text-center">
            <p class="font-display text-xl text-stone-800 mb-1">Terima kasih, {{ $guest->name }}!</p>
            <p class="text-stone-500 text-sm" id="rsvp-msg"></p>

            <div id="qr-wrap" class="hidden mt-8">
                <p class="text-stone-400 text-xs tracking-widest uppercase mb-4">QR Code Kehadiran</p>
                <div class="inline-block p-3 bg-white border border-stone-200">
                    <img id="qr-img" src="" alt="QR Code kehadiran" class="w-48 h-48">
                </div>
                <p class="text-stone-400 text-xs mt-3">Tunjukkan kepada panitia saat tiba di lokasi</p>
            </div>

            <button id="rsvp-edit" class="mt-6 text-xs text-stone-500 underline underline-offset-2">Ubah konfirmasi</button>
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
        $rsvpUrl = $guest->short_token
            ? route('rsvp.store.short', [$wedding->short_id ?? $wedding->public_id, $wedding->slug, $guest->short_token])
            : route('rsvp.store', [$wedding->public_id, $wedding->slug, $guest->invitation_token]);
        $qrUrl = route('rsvp.qr', [
            'anyId' => $wedding->short_id ?? $wedding->public_id,
            'slug'  => $wedding->slug,
            'token' => $guest->short_token ?? $guest->invitation_token,
        ]);
    @endphp

    var ACTION = @json($rsvpUrl);
    var QR_URL = @json($qrUrl);
    var CSRF   = form.querySelector('[name=_token]').value;

    @if($guest->rsvp)
    showSuccess({{ $guest->rsvp->attendance_status === 'attending' ? 'true' : 'false' }}, false);
    @endif

    form.addEventListener('submit', function (e) {
        e.preventDefault();
        clearErrors();
        btn.disabled = true;
        btn.textContent = 'Mengirim...';

        fetch(ACTION, {
            method: 'POST',
            headers: { 'X-CSRF-TOKEN': CSRF, 'Accept': 'application/json' },
            body: new FormData(form),
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
            qrImg.src = loadQr ? QR_URL + '?t=' + Date.now() : QR_URL;
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
