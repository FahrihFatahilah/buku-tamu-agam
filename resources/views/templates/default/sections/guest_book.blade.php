@php
    $entries = \App\Models\GuestBookEntry::where('wedding_id', $wedding->id)
        ->approved()->latest()->limit(20)->get();
    $invId = $wedding->short_id ?? $wedding->public_id;
    $gbUrl = url("/{$invId}/{$wedding->slug}/guestbook") . ($guest ? '?t=' . $guest->invitation_token : '');
@endphp

<section class="py-20 px-6 bg-stone-50" x-data="defaultGuestBook('{{ $gbUrl }}', '{{ csrf_token() }}')">
    <div class="max-w-xl mx-auto">
        <div class="text-center mb-10">
            <p class="text-xs tracking-[0.3em] uppercase text-stone-400 mb-3">Ucapan &amp; Doa</p>
            <h2 class="font-display text-3xl text-stone-800">Buku Tamu</h2>
        </div>

        <div x-show="sent" x-transition
            class="mb-6 px-4 py-3 bg-green-50 border border-green-200 text-green-700 text-sm text-center">
            Ucapan berhasil dikirim. Terima kasih!
        </div>

        <div x-show="error" x-transition
            class="mb-6 px-4 py-3 bg-red-50 border border-red-200 text-red-600 text-sm text-center">
            <span x-text="error"></span>
        </div>

        <form @submit.prevent="submit" class="space-y-4 mb-12" x-show="!sent">
            <div>
                <input type="text" x-model="form.name" placeholder="Nama Anda"
                    {{ $guest ? 'readonly' : '' }}
                    class="w-full px-4 py-3 border border-stone-200 bg-white text-sm placeholder-stone-400 focus:outline-none focus:border-stone-400 read-only:bg-stone-100">
                <p x-show="errors.name" x-text="errors.name" class="mt-1 text-xs text-red-500"></p>
            </div>

            <div>
                <textarea x-model="form.message" rows="3" placeholder="Tulis ucapan dan doa untuk kedua mempelai..."
                    class="w-full px-4 py-3 border border-stone-200 bg-white text-sm placeholder-stone-400 focus:outline-none focus:border-stone-400 resize-none"></textarea>
                <p x-show="errors.message" x-text="errors.message" class="mt-1 text-xs text-red-500"></p>
            </div>

            <button type="submit" :disabled="loading"
                class="w-full py-3 border border-stone-300 text-stone-700 text-sm tracking-wider hover:border-stone-500 transition-colors disabled:opacity-50">
                <span x-show="!loading">Kirim Ucapan</span>
                <span x-show="loading">Mengirim...</span>
            </button>
        </form>

        <div class="space-y-4">
            @foreach($entries as $entry)
            <div class="border-b border-stone-200 pb-4 last:border-0">
                <div class="flex items-baseline justify-between gap-2 mb-1">
                    <p class="font-display text-stone-800 text-sm">{{ $entry->name }}</p>
                    <time class="text-stone-400 text-xs shrink-0">{{ $entry->created_at->diffForHumans() }}</time>
                </div>
                <p class="text-stone-500 text-sm leading-relaxed">{{ $entry->message }}</p>
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
