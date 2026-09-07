@extends('admin.layout')

@section('title', 'Domain — ' . $wedding->coupleName())

@section('content')
<div class="max-w-2xl">
    <div class="flex items-center justify-between mb-6">
        <div>
            <p class="text-xs text-stone-400 mb-0.5">
                <a href="{{ route('admin.weddings.edit', $wedding) }}" class="hover:text-stone-600">{{ $wedding->coupleName() }}</a>
                <span class="mx-1">›</span> Domain
            </p>
            <h1 class="text-xl font-semibold text-stone-800">Domain</h1>
        </div>
        <button onclick="document.getElementById('modal-add').classList.remove('hidden')"
            class="px-4 py-2 bg-stone-800 text-white text-sm hover:bg-stone-700 transition-colors">
            + Tambah Domain
        </button>
    </div>

    <div class="bg-white border border-stone-200">
        @if($domains->isEmpty())
        <div class="px-6 py-12 text-center text-stone-400 text-sm">Belum ada domain.</div>
        @else
        <table class="w-full text-sm">
            <thead>
                <tr class="border-b border-stone-200 bg-stone-50">
                    <th class="text-left px-4 py-3 text-xs font-medium text-stone-500 uppercase tracking-wide">Domain</th>
                    <th class="text-left px-4 py-3 text-xs font-medium text-stone-500 uppercase tracking-wide">Tipe</th>
                    <th class="text-left px-4 py-3 text-xs font-medium text-stone-500 uppercase tracking-wide">Status</th>
                    <th class="px-4 py-3"></th>
                </tr>
            </thead>
            <tbody class="divide-y divide-stone-100">
                @foreach($domains as $domain)
                <tr class="hover:bg-stone-50 transition-colors">
                    <td class="px-4 py-3">
                        <p class="font-mono text-sm text-stone-800">{{ $domain->domain }}</p>
                        @if($domain->is_primary)
                        <span class="text-xs text-stone-400">Utama</span>
                        @endif
                    </td>
                    <td class="px-4 py-3 text-stone-500">{{ $domain->type }}</td>
                    <td class="px-4 py-3">
                        @if($domain->verification_status === 'verified')
                        <span class="inline-flex items-center gap-1.5 text-xs text-green-700">
                            <span class="w-1.5 h-1.5 rounded-full bg-green-500"></span>Terverifikasi
                        </span>
                        @elseif($domain->verification_status === 'pending')
                        <span class="inline-flex items-center gap-1.5 text-xs text-amber-600">
                            <span class="w-1.5 h-1.5 rounded-full bg-amber-400"></span>Menunggu
                        </span>
                        @else
                        <span class="inline-flex items-center gap-1.5 text-xs text-red-500">
                            <span class="w-1.5 h-1.5 rounded-full bg-red-400"></span>Gagal
                        </span>
                        @endif
                    </td>
                    <td class="px-4 py-3">
                        <div class="flex items-center justify-end gap-2">
                            @if($domain->verification_status !== 'verified' && $domain->type === 'custom')
                            <form method="POST" action="{{ route('admin.weddings.domains.verify', [$wedding, $domain]) }}">
                                @csrf
                                <button type="submit" class="text-xs text-stone-400 hover:text-stone-600 transition-colors">Verifikasi</button>
                            </form>
                            @endif
                            @if(!$domain->is_primary)
                            <form method="POST" action="{{ route('admin.weddings.domains.destroy', [$wedding, $domain]) }}">
                                @csrf @method('DELETE')
                                <button type="submit" class="text-xs text-red-400 hover:text-red-600 transition-colors"
                                    onclick="return confirm('Hapus domain ini?')">Hapus</button>
                            </form>
                            @endif
                        </div>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
        @endif
    </div>

    {{-- Custom domain instructions --}}
    <div class="mt-6 p-4 bg-stone-50 border border-stone-200 text-xs text-stone-500 space-y-1">
        <p class="font-medium text-stone-600">Cara verifikasi custom domain:</p>
        <p>1. Tambahkan DNS TXT record pada domain Anda:</p>
        <p class="font-mono bg-white border border-stone-200 px-2 py-1 mt-1">_ngundang-verify.yourdomain.com → [verification token]</p>
        <p>2. Klik tombol "Verifikasi" setelah DNS propagasi selesai (biasanya 5–30 menit).</p>
        <p>3. Arahkan DNS A record domain ke IP server: <span class="font-mono">{{ gethostbyname(parse_url(config('app.url'), PHP_URL_HOST) ?: 'server') }}</span></p>
    </div>
</div>

{{-- Modal Add --}}
<div id="modal-add" class="hidden fixed inset-0 bg-black/40 z-50 flex items-center justify-center p-4">
    <div class="bg-white w-full max-w-sm">
        <div class="flex items-center justify-between px-5 py-4 border-b border-stone-200">
            <h2 class="text-sm font-semibold text-stone-800">Tambah Domain</h2>
            <button onclick="document.getElementById('modal-add').classList.add('hidden')" class="text-stone-400 hover:text-stone-600">✕</button>
        </div>
        <form method="POST" action="{{ route('admin.weddings.domains.store', $wedding) }}" class="px-5 py-4 space-y-3">
            @csrf
            <div>
                <label class="block text-xs text-stone-500 mb-1">Tipe</label>
                <select name="type" id="domain-type" onchange="toggleDomainHint()"
                    class="w-full border border-stone-200 px-3 py-2 text-sm focus:outline-none focus:border-stone-400">
                    <option value="subdomain">Subdomain (*.{{ config('ngundang.platform_domain', 'ngundang.com') }})</option>
                    <option value="custom">Custom Domain</option>
                </select>
            </div>
            <div>
                <label class="block text-xs text-stone-500 mb-1">Domain <span class="text-red-400">*</span></label>
                <input type="text" name="domain" required placeholder="bagasrani"
                    id="domain-input"
                    class="w-full border border-stone-200 px-3 py-2 text-sm focus:outline-none focus:border-stone-400">
                <p id="domain-hint" class="mt-1 text-xs text-stone-400">
                    Masukkan subdomain saja, misal: <span class="font-mono">bagasrani</span>
                </p>
            </div>
            @error('domain')<p class="text-xs text-red-500">{{ $message }}</p>@enderror
            <div class="flex justify-end gap-2 pt-1">
                <button type="button" onclick="document.getElementById('modal-add').classList.add('hidden')"
                    class="px-4 py-2 text-sm text-stone-500 hover:text-stone-700">Batal</button>
                <button type="submit" class="px-4 py-2 bg-stone-800 text-white text-sm hover:bg-stone-700 transition-colors">Tambah</button>
            </div>
        </form>
    </div>
</div>

<script>
function toggleDomainHint() {
    const type = document.getElementById('domain-type').value;
    const hint = document.getElementById('domain-hint');
    const input = document.getElementById('domain-input');
    if (type === 'subdomain') {
        hint.textContent = 'Masukkan subdomain saja, misal: bagasrani';
        input.placeholder = 'bagasrani';
    } else {
        hint.textContent = 'Masukkan domain lengkap, misal: bagasrani.com';
        input.placeholder = 'bagasrani.com';
    }
}
</script>
@endsection
