<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Check-in — {{ $wedding->coupleName() }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-stone-900 text-stone-100 min-h-screen font-sans antialiased" x-data="checkin()">

<div class="max-w-md mx-auto px-4 py-8">
    <div class="mb-8 text-center">
        <p class="text-stone-400 text-xs tracking-widest uppercase mb-1">Check-in</p>
        <h1 class="font-serif text-xl text-stone-100">{{ $wedding->coupleName() }}</h1>
    </div>

    {{-- QR Scanner --}}
    <div class="bg-stone-800 border border-stone-700 p-5 mb-5">
        <div class="flex items-center justify-between mb-3">
            <p class="text-xs text-stone-400 tracking-wider uppercase">Scan QR Code</p>
            <button @click="switchCamera()" class="text-xs text-stone-400 hover:text-stone-200 px-2 py-1 border border-stone-600 hover:border-stone-400 transition-colors">Ganti Kamera</button>
        </div>
        <div id="qr-reader" class="w-full aspect-square bg-stone-900" :style="mirrored ? 'transform:scaleX(-1)' : ''"></div>
        <p class="text-xs text-stone-500 mt-2 text-center">Arahkan kamera ke QR code tamu</p>
        <p x-show="scanError" x-text="scanError" class="mt-2 text-xs text-red-400 text-center"></p>
    </div>

    {{-- Manual Search --}}
    <div class="bg-stone-800 border border-stone-700 p-5 mb-5">
        <p class="text-xs text-stone-400 mb-3 tracking-wider uppercase">Cari Tamu</p>
        <div class="flex gap-2">
            <input
                type="text"
                x-model="searchQuery"
                @input.debounce.400ms="search()"
                placeholder="Nama atau nomor telepon..."
                class="flex-1 px-3 py-2 bg-stone-900 border border-stone-600 text-stone-100 text-sm placeholder-stone-500 focus:outline-none focus:border-stone-400"
            >
        </div>

        <div x-show="searchResults.length > 0" class="mt-3 space-y-2">
            <template x-for="g in searchResults" :key="g.id">
                <button
                    @click="selectGuest(g)"
                    class="w-full text-left px-3 py-2.5 bg-stone-900 border border-stone-700 hover:border-stone-500 transition-colors"
                >
                    <p class="text-sm text-stone-100" x-text="g.name"></p>
                    <p class="text-xs text-stone-400 mt-0.5" x-text="g.category + ' · ' + g.max_pax + ' orang'"></p>
                </button>
            </template>
        </div>
    </div>

    {{-- Guest Card --}}
    <div x-show="guest" class="bg-stone-800 border border-stone-600 p-5">
        <div class="mb-4">
            <p class="text-xs text-stone-400 tracking-wider uppercase mb-1">Tamu</p>
            <p class="font-serif text-xl text-stone-100" x-text="guest?.name"></p>
            <p class="text-sm text-stone-400 mt-0.5" x-text="guest?.category"></p>
        </div>

        <div x-show="guest?.is_checked_in" class="mb-4 px-3 py-2 bg-green-900/30 border border-green-700/30 text-green-400 text-sm">
            Sudah check-in pukul <span x-text="guest?.checkin?.checked_in_at"></span>
            (<span x-text="guest?.checkin?.pax"></span> orang)
        </div>

        <div class="mb-4">
            <label class="block text-xs text-stone-400 mb-1.5">Jumlah Tamu (maks. <span x-text="guest?.max_pax"></span>)</label>
            <input
                type="number"
                x-model="pax"
                :min="1"
                :max="guest?.max_pax"
                class="w-full px-3 py-2 bg-stone-900 border border-stone-600 text-stone-100 text-sm focus:outline-none focus:border-stone-400"
            >
        </div>

        <button
            @click="confirm()"
            :disabled="loading"
            class="w-full py-3 bg-stone-100 text-stone-900 text-sm font-medium hover:bg-white transition-colors disabled:opacity-50"
        >
            <span x-show="!loading">Konfirmasi Check-in</span>
            <span x-show="loading">Memproses...</span>
        </button>
    </div>

    {{-- Success --}}
    <div x-show="success" class="bg-green-900/20 border border-green-700/30 p-5 text-center">
        <p class="text-green-400 font-medium" x-text="successMessage"></p>
    </div>
</div>

<script src="https://unpkg.com/html5-qrcode@2.3.8/html5-qrcode.min.js"></script>
<script>
function checkin() {
    return {
        guest: null,
        pax: 1,
        searchQuery: '',
        searchResults: [],
        loading: false,
        success: false,
        successMessage: '',
        scanner: null,
        facingMode: 'environment',
        mirrored: false,
        scanError: '',
        _lastToken: null,

        init() {
            this.initScanner();
        },

        initScanner() {
            if (this.scanner) {
                this.scanner.stop().catch(() => {});
            }
            this.scanner = new Html5Qrcode('qr-reader');
            this.scanner.start(
                { facingMode: this.facingMode },
                { fps: 10, qrbox: { width: 250, height: 250 } },
                (decodedText) => this.onScan(decodedText),
                () => {}
            ).catch(() => {
                // fallback ke kamera manapun
                this.scanner.start(
                    { facingMode: 'user' },
                    { fps: 10, qrbox: { width: 250, height: 250 } },
                    (decodedText) => this.onScan(decodedText),
                    () => {}
                ).catch(() => {});
            });
        },

        switchCamera() {
            this.scanner.stop().then(() => {
                this.facingMode = this.facingMode === 'environment' ? 'user' : 'environment';
                this.mirrored  = this.facingMode === 'user';
                this.initScanner();
            }).catch(() => {});
        },

        async onScan(text) {
            // Match both short token (12 alphanumeric) and full token (64 hex)
            const match = text.match(/\/u\/([a-zA-Z0-9]{12,64})/);
            if (!match) return;

            const token = match[1];
            if (this._lastToken === token) return; // debounce scan sama
            this._lastToken = token;
            await this.resolveToken(token);
        },

        async resolveToken(token) {
            const res = await fetch(`/check-in/{{ $wedding->id }}/token/${token}`, {
                headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' }
            });

            if (res.ok) {
                const data = await res.json();
                this.guest = data.guest;
                this.pax = this.guest.max_pax;
                this.scanError = '';
            } else if (res.status === 404) {
                this.scanError = 'QR tidak dikenali. Tamu tidak ditemukan.';
                this.guest = null;
                setTimeout(() => { this.scanError = ''; this._lastToken = null; }, 3000);
            } else {
                this.scanError = 'Gagal membaca QR. Coba lagi.';
                setTimeout(() => { this.scanError = ''; this._lastToken = null; }, 3000);
            }
        },

        async search() {
            if (this.searchQuery.length < 2) {
                this.searchResults = [];
                return;
            }

            const res = await fetch(`/check-in/{{ $wedding->id }}/search?q=${encodeURIComponent(this.searchQuery)}`, {
                headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' }
            });

            if (res.ok) {
                const data = await res.json();
                this.searchResults = data.guests;
            }
        },

        selectGuest(g) {
            this.guest = g;
            this.pax = g.max_pax;
            this.searchResults = [];
            this.searchQuery = '';
        },

        async confirm() {
            if (!this.guest) return;
            this.loading = true;

            const res = await fetch(`/check-in/{{ $wedding->id }}/confirm`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || '',
                    'X-Requested-With': 'XMLHttpRequest',
                },
                body: JSON.stringify({ token: this.guest.token, pax: this.pax }),
            });

            this.loading = false;

            if (res.ok) {
                const data = await res.json();
                this.successMessage = `${data.guest_name} — ${data.pax} orang — ${data.checked_in_at}`;
                this.success = true;
                this.guest = null;
                setTimeout(() => { this.success = false; }, 4000);
            }
        },
    };
}
</script>
</body>
</html>
