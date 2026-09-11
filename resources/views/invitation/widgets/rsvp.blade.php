@php
    $p = $node['props'] ?? [];
    $nodeId = preg_replace('/[^A-Za-z0-9_-]/', '', (string) ($node['id'] ?? ''));
    $submitLabel = $p['submit'] ?? 'Kirim Konfirmasi';

    $rsvpUrl = route('rsvp.store.short', [
        $wedding->short_id ?? $wedding->public_id,
        $wedding->slug,
        $guest->short_token ?? $guest->invitation_token,
    ]);
    $qrUrl = route('rsvp.qr', [
        'anyId' => $wedding->short_id ?? $wedding->public_id,
        'slug' => $wedding->slug,
        'token' => $guest->short_token ?? $guest->invitation_token,
    ]);
@endphp

<div class="n-inner" data-rsvp="{{ $nodeId }}"
    data-action="{{ $rsvpUrl }}"
    data-qr="{{ $qrUrl }}"
    data-has-rsvp="{{ $guest->rsvp ? '1' : '0' }}"
    data-attending="{{ $guest->rsvp?->attendance_status === 'attending' ? '1' : '0' }}"
    data-submit-label="{{ $submitLabel }}">

    <div class="text-center mb-10">
        @if(!empty($p['eyebrow']))
        <p class="text-xs tracking-[0.3em] uppercase opacity-60 mb-3" data-edit-prop="eyebrow">{{ $p['eyebrow'] }}</p>
        @endif
        @if(!empty($p['heading']))
        <h2 class="n-display text-3xl" data-edit-prop="heading">{{ $p['heading'] }}</h2>
        @endif
    </div>

    <div data-rsvp-form>
        <form class="space-y-5" onsubmit="return false;">
            @csrf
            <div>
                <span class="block text-sm opacity-70 mb-2">Kehadiran</span>
                <div class="space-y-2">
                    @foreach(['attending' => 'Hadir', 'not_attending' => 'Tidak Hadir', 'maybe' => 'Mungkin Hadir'] as $value => $label)
                    <label class="flex items-center gap-3 cursor-pointer">
                        <input type="radio" name="attendance_status" value="{{ $value }}"
                            {{ $guest->rsvp?->attendance_status === $value ? 'checked' : '' }}>
                        <span class="text-sm">{{ $label }}</span>
                    </label>
                    @endforeach
                </div>
                <p data-err="attendance_status" class="mt-1 text-xs text-red-500 hidden"></p>
            </div>

            <div>
                <label class="block text-sm opacity-70 mb-1.5">Jumlah Tamu (maks. {{ $guest->max_pax }})</label>
                <input type="number" name="pax" min="1" max="{{ $guest->max_pax }}"
                    value="{{ $guest->rsvp?->pax ?? 1 }}"
                    class="w-full px-3 py-2 text-sm"
                    style="border: 1px solid color-mix(in srgb, currentColor 25%, transparent); background: transparent; color: inherit;">
                <p data-err="pax" class="mt-1 text-xs text-red-500 hidden"></p>
            </div>

            <div>
                <label class="block text-sm opacity-70 mb-1.5">Pesan (opsional)</label>
                <textarea name="note" rows="2" class="w-full px-3 py-2 text-sm resize-none"
                    style="border: 1px solid color-mix(in srgb, currentColor 25%, transparent); background: transparent; color: inherit;">{{ $guest->rsvp?->note }}</textarea>
            </div>

            <button type="submit" data-rsvp-submit
                class="w-full py-3 text-sm tracking-wider"
                style="background: var(--n-primary, #7C3238); color: #fff;">
                {{ $submitLabel }}
            </button>
        </form>
    </div>

    <div data-rsvp-success class="hidden text-center">
        <p class="n-display text-xl mb-1">Terima kasih, {{ $guest->name }}!</p>
        <p class="text-sm opacity-70" data-rsvp-msg></p>
        <div data-rsvp-qr class="hidden mt-8">
            <p class="text-xs tracking-widest uppercase opacity-60 mb-4">QR Code Kehadiran</p>
            <img data-rsvp-qr-img src="" alt="QR Code kehadiran" class="w-48 h-48 mx-auto">
            <p class="text-xs opacity-60 mt-3">Tunjukkan kepada panitia saat tiba di lokasi</p>
        </div>
        <button type="button" data-rsvp-edit class="mt-6 text-xs underline underline-offset-2 opacity-70">
            Ubah konfirmasi
        </button>
    </div>
</div>

<script>
(function () {
    var root = document.querySelector('[data-rsvp="{{ $nodeId }}"]');
    if (!root || root.dataset.bound === '1') return;
    root.dataset.bound = '1';

    var form    = root.querySelector('[data-rsvp-form]');
    var success = root.querySelector('[data-rsvp-success]');
    var msg     = root.querySelector('[data-rsvp-msg]');
    var qrWrap  = root.querySelector('[data-rsvp-qr]');
    var qrImg   = root.querySelector('[data-rsvp-qr-img]');
    var editBtn = root.querySelector('[data-rsvp-edit]');
    var btn     = root.querySelector('[data-rsvp-submit]');
    var label   = root.dataset.submitLabel;

    function showSuccess(attending, loadQr) {
        form.classList.add('hidden');
        success.classList.remove('hidden');
        msg.textContent = attending
            ? 'Konfirmasi kehadiran Anda telah kami terima.'
            : 'Kami mengerti, semoga bisa hadir di lain kesempatan.';
        if (attending) {
            qrWrap.classList.remove('hidden');
            qrImg.src = loadQr ? root.dataset.qr + '?t=' + Date.now() : root.dataset.qr;
        }
    }

    if (root.dataset.hasRsvp === '1' && form) {
        showSuccess(root.dataset.attending === '1', false);
    }

    root.querySelectorAll('form').forEach(function (f) {
        f.addEventListener('submit', function (e) {
            e.preventDefault();
            if (!btn) return;

            btn.disabled = true;
            btn.textContent = 'Mengirim...';

            fetch(root.dataset.action, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || '',
                    'Accept': 'application/json',
                },
                body: new FormData(f),
            })
            .then(function (r) { return r.json(); })
            .then(function (res) {
                if (res.success) {
                    showSuccess(res.attending, true);
                } else if (res.errors) {
                    Object.keys(res.errors).forEach(function (k) {
                        var el = root.querySelector('[data-err="' + k + '"]');
                        if (el) { el.textContent = res.errors[k][0]; el.classList.remove('hidden'); }
                    });
                    btn.disabled = false;
                    btn.textContent = label;
                }
            })
            .catch(function () {
                btn.disabled = false;
                btn.textContent = label;
            });
        });
    });

    if (editBtn) {
        editBtn.addEventListener('click', function () {
            success.classList.add('hidden');
            form.classList.remove('hidden');
            if (btn) { btn.disabled = false; btn.textContent = label; }
        });
    }
})();
</script>
