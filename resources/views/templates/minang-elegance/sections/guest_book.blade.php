@php
    $gbSection = $sections->firstWhere('section_key', 'guest_book');
    $entries   = \App\Models\GuestBookEntry::where('wedding_id', $wedding->id)
        ->approved()->latest()->limit(20)->get();
    // Use short_id if available, else public_id
    $invId = $wedding->short_id ?? $wedding->public_id;
    $gbUrl = url("/{$invId}/{$wedding->slug}/guestbook") . ($guest ? '?t=' . $guest->short_token : '');
@endphp

<section id="guest_book" class="section-bg relative py-20 px-6"
    x-data="guestBook('{{ $gbUrl }}', '{{ csrf_token() }}')">

    @include('templates._section-bg', ['section' => $gbSection, 'defaultBg' => '#2C1810'])

    <div class="section-content max-w-xl mx-auto">
        <div class="text-center mb-10 reveal">
            <p class="text-[#B8960C] text-xs tracking-[0.3em] uppercase mb-3">Ucapan & Doa</p>
            <h2 class="font-serif text-[#F5F0E8] text-2xl">Buku Tamu</h2>
        </div>

        {{-- Success message --}}
        <div x-show="sent" x-transition
            class="mb-6 px-4 py-3 bg-green-900/30 border border-green-700/30 text-green-400 text-sm text-center reveal">
            Ucapan berhasil dikirim. Terima kasih!
        </div>

        {{-- Error message --}}
        <div x-show="error" x-transition
            class="mb-6 px-4 py-3 bg-red-900/30 border border-red-700/30 text-red-400 text-sm text-center">
            <span x-text="error"></span>
        </div>

        {{-- Form --}}
        <form @submit.prevent="submit" class="space-y-4 mb-12 reveal" x-show="!sent">
            <div>
                <input type="text" x-model="form.name"
                    placeholder="Nama Anda"
                    value="{{ $guest?->name }}"
                    {{ $guest ? 'readonly' : '' }}
                    class="w-full px-4 py-3 bg-[#F5F0E8]/5 border border-[#F5F0E8]/10 text-[#F5F0E8] text-sm placeholder-[#F5F0E8]/30 focus:outline-none focus:border-[#B8960C]/50 transition-colors">
                <p x-show="errors.name" x-text="errors.name" class="mt-1 text-xs text-red-400"></p>
            </div>

            <div>
                <textarea x-model="form.message" rows="3"
                    placeholder="Tulis ucapan dan doa untuk kedua mempelai..."
                    class="w-full px-4 py-3 bg-[#F5F0E8]/5 border border-[#F5F0E8]/10 text-[#F5F0E8] text-sm placeholder-[#F5F0E8]/30 focus:outline-none focus:border-[#B8960C]/50 transition-colors resize-none"></textarea>
                <p x-show="errors.message" x-text="errors.message" class="mt-1 text-xs text-red-400"></p>
            </div>

            <button type="submit"
                :disabled="loading"
                class="w-full py-3 border border-[#B8960C]/50 text-[#B8960C] text-sm tracking-wider hover:bg-[#B8960C]/10 transition-colors focus:outline-none disabled:opacity-50">
                <span x-show="!loading">Kirim Ucapan</span>
                <span x-show="loading">Mengirim...</span>
            </button>
        </form>

        {{-- Approved messages --}}
        <div class="space-y-4 stagger-children" id="gb-entries">
            @foreach($entries as $entry)
            <div class="border-b border-[#F5F0E8]/10 pb-4 last:border-0">
                <div class="flex items-baseline justify-between gap-2 mb-1">
                    <p class="font-serif text-[#F5F0E8] text-sm">{{ $entry->name }}</p>
                    <time class="text-[#F5F0E8]/30 text-xs shrink-0">{{ $entry->created_at->diffForHumans() }}</time>
                </div>
                <p class="text-[#F5F0E8]/60 text-sm leading-relaxed">{{ $entry->message }}</p>
            </div>
            @endforeach
        </div>
    </div>
</section>

<script>
function guestBook(url, token) {
    return {
        url, token,
        form: { name: {{ json_encode($guest?->name ?? '') }}, message: '' },
        loading: false,
        sent: false,
        error: null,
        errors: {},

        async submit() {
            this.loading = true;
            this.error = null;
            this.errors = {};

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
                    // Validation errors
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
