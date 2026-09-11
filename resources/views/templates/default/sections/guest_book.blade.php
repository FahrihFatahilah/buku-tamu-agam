@php
    $entries = \App\Models\GuestBookEntry::where('wedding_id', $wedding->id)
        ->approved()->latest()->limit(20)->get();
    $invId = $wedding->short_id ?? $wedding->public_id;
    $gbUrl = url("/{$invId}/{$wedding->slug}/guestbook") . ($guest ? '?t=' . $guest->invitation_token : '');
@endphp

<section id="guest_book" class="py-20 px-6 tpl-panel" x-data="defaultGuestBook('{{ $gbUrl }}', '{{ csrf_token() }}')">
    <div class="max-w-xl mx-auto">
        <div class="text-center mb-10">
            <p class="text-xs tracking-[0.3em] uppercase tpl-faint mb-3">Ucapan &amp; Doa</p>
            <h2 class="tpl-display text-3xl tpl-ink">Buku Tamu</h2>
        </div>

        <div x-show="sent" x-transition
            class="mb-6 px-4 py-3 border tpl-hairline tpl-surface text-sm text-center tpl-primary">
            Ucapan berhasil dikirim. Terima kasih!
        </div>

        <div x-show="error" x-transition
            class="mb-6 px-4 py-3 bg-red-500/10 border border-red-500/30 text-red-500 text-sm text-center">
            <span x-text="error"></span>
        </div>

        <form @submit.prevent="submit" class="space-y-4 mb-12" x-show="!sent">
            <div>
                <input type="text" x-model="form.name" placeholder="Nama Anda"
                    {{ $guest ? 'readonly' : '' }}
                    class="tpl-field w-full px-4 py-3 border text-sm focus:outline-none read-only:opacity-60">
                <p x-show="errors.name" x-text="errors.name" class="mt-1 text-xs text-red-500"></p>
            </div>

            <div>
                <textarea x-model="form.message" rows="3" placeholder="Tulis ucapan dan doa untuk kedua mempelai..."
                    class="tpl-field w-full px-4 py-3 border text-sm focus:outline-none resize-none"></textarea>
                <p x-show="errors.message" x-text="errors.message" class="mt-1 text-xs text-red-500"></p>
            </div>

            <button type="submit" :disabled="loading"
                class="tpl-btn w-full py-3 border text-sm tracking-wider transition-colors disabled:opacity-50">
                <span x-show="!loading">Kirim Ucapan</span>
                <span x-show="loading">Mengirim...</span>
            </button>
        </form>

        <div class="space-y-4">
            @foreach($entries as $entry)
            <div class="border-b tpl-hairline pb-4 last:border-0">
                <div class="flex items-baseline justify-between gap-2 mb-1">
                    <p class="tpl-display tpl-primary text-sm">{{ $entry->name }}</p>
                    <time class="tpl-faint text-xs shrink-0">{{ $entry->created_at->diffForHumans() }}</time>
                </div>
                <p class="tpl-muted text-sm leading-relaxed">{{ $entry->message }}</p>
            </div>
            @endforeach
        </div>
    </div>
</section>

<script>
function defaultGuestBook(url, token) {
    return {
        url, token,
        form: { name: @json($guest?->name ?? ''), message: '' },
        loading: false, sent: false, error: null, errors: {},

        async submit() {
            this.loading = true; this.error = null; this.errors = {};
            try {
                const res = await fetch(this.url, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': this.token,
                        'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest',
                    },
                    body: JSON.stringify(this.form),
                });
                const data = await res.json();

                if (res.status === 422) {
                    this.errors = data.errors ?? {};
                } else if (data.success) {
                    this.sent = true;
                    this.form.message = '';
                } else {
                    this.error = data.message ?? 'Terjadi kesalahan.';
                }
            } catch (e) {
                this.error = 'Gagal mengirim. Periksa koneksi internet Anda.';
            } finally {
                this.loading = false;
            }
        }
    };
}
</script>
