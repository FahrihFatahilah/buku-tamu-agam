@php
    $p = $node['props'] ?? [];
    $nodeId = preg_replace('/[^A-Za-z0-9_-]/', '', (string) ($node['id'] ?? ''));
    $limit = max(5, min(50, (int) ($p['limit'] ?? 20)));

    $entries = \App\Models\GuestBookEntry::where('wedding_id', $wedding->id)
        ->approved()->latest()->limit($limit)->get();

    $invId = $wedding->short_id ?? $wedding->public_id;
    $gbUrl = url("/{$invId}/{$wedding->slug}/guestbook") . ($guest ? '?t=' . $guest->invitation_token : '');
@endphp

<div class="n-inner" data-guestbook="{{ $nodeId }}" data-url="{{ $gbUrl }}">
    <div class="text-center mb-10">
        @if(!empty($p['eyebrow']))
        <p class="text-xs tracking-[0.3em] uppercase opacity-60 mb-3" data-edit-prop="eyebrow">{{ $p['eyebrow'] }}</p>
        @endif
        @if(!empty($p['heading']))
        <h2 class="n-display text-3xl" data-edit-prop="heading">{{ $p['heading'] }}</h2>
        @endif
    </div>

    <p data-gb-sent class="hidden mb-6 px-4 py-3 text-sm text-center"
        style="border: 1px solid color-mix(in srgb, currentColor 20%, transparent);">Ucapan berhasil dikirim. Terima kasih!</p>
    <p data-gb-error class="hidden mb-6 px-4 py-3 text-sm text-center text-red-500"></p>

    <form data-gb-form class="space-y-4 mb-12" onsubmit="return false;">
        <div>
            <input type="text" name="name" placeholder="Nama Anda" value="{{ $guest?->name }}" {{ $guest ? 'readonly' : '' }}
                class="w-full px-4 py-3 text-sm"
                style="border: 1px solid color-mix(in srgb, currentColor 25%, transparent); background: transparent; color: inherit;">
            <p data-gb-err="name" class="mt-1 text-xs text-red-500 hidden"></p>
        </div>
        <div>
            <textarea name="message" rows="3" placeholder="Tulis ucapan dan doa untuk kedua mempelai..."
                class="w-full px-4 py-3 text-sm resize-none"
                style="border: 1px solid color-mix(in srgb, currentColor 25%, transparent); background: transparent; color: inherit;"></textarea>
            <p data-gb-err="message" class="mt-1 text-xs text-red-500 hidden"></p>
        </div>
        <button type="submit" data-gb-submit class="w-full py-3 text-sm tracking-wider"
            style="border: 1px solid var(--n-accent, #B8960C); color: var(--n-accent, #B8960C);"
            data-edit-prop="submit">{{ $p['submit'] ?? 'Kirim Ucapan' }}</button>
    </form>

    <div class="space-y-4">
        @foreach($entries as $entry)
        <div class="pb-4" style="border-bottom: 1px solid color-mix(in srgb, currentColor 12%, transparent);">
            <div class="flex items-baseline justify-between gap-2 mb-1">
                <p class="text-sm font-medium">{{ $entry->name }}</p>
                <time class="text-xs opacity-50 shrink-0">{{ $entry->created_at->diffForHumans() }}</time>
            </div>
            <p class="text-sm opacity-80 leading-relaxed">{{ $entry->message }}</p>
        </div>
        @endforeach
    </div>
</div>

<script>
(function () {
    var root = document.querySelector('[data-guestbook="{{ $nodeId }}"]');
    if (!root || root.dataset.bound === '1') return;
    root.dataset.bound = '1';

    var form  = root.querySelector('[data-gb-form]');
    var sent  = root.querySelector('[data-gb-sent]');
    var error = root.querySelector('[data-gb-error]');
    var btn   = root.querySelector('[data-gb-submit]');

    if (!form) return;

    form.addEventListener('submit', function (e) {
        e.preventDefault();
        error.classList.add('hidden');

        var data = {
            name: form.querySelector('[name=name]').value,
            message: form.querySelector('[name=message]').value,
        };

        btn.disabled = true;
        btn.textContent = 'Mengirim...';

        fetch(root.dataset.url, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || '',
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest',
            },
            body: JSON.stringify(data),
        })
        .then(function (r) { return r.json(); })
        .then(function (res) {
            if (res.errors) {
                Object.keys(res.errors).forEach(function (k) {
                    var el = root.querySelector('[data-gb-err="' + k + '"]');
                    if (el) { el.textContent = res.errors[k][0]; el.classList.remove('hidden'); }
                });
            } else if (res.success) {
                form.classList.add('hidden');
                sent.classList.remove('hidden');
            } else {
                error.textContent = res.message || 'Terjadi kesalahan.';
                error.classList.remove('hidden');
            }
        })
        .catch(function () {
            error.textContent = 'Gagal mengirim. Periksa koneksi internet Anda.';
            error.classList.remove('hidden');
        })
        .finally(function () {
            btn.disabled = false;
            btn.textContent = @json($p['submit'] ?? 'Kirim Ucapan');
        });
    });
})();
</script>
