@extends('admin.layout')

@section('title', 'Tamu — ' . $wedding->coupleName())

@section('content')
<div class="max-w-6xl">

    {{-- Header --}}
    <div class="flex items-center justify-between mb-6">
        <div>
            <p class="text-xs text-stone-400 mb-0.5">
                <a href="{{ route('admin.weddings.index') }}" class="hover:text-stone-600">Undangan</a>
                <span class="mx-1">›</span>
                {{ $wedding->coupleName() }}
            </p>
            <h1 class="text-xl font-semibold text-stone-800">Daftar Tamu</h1>
        </div>
        <button onclick="document.getElementById('modal-add').classList.remove('hidden')"
            class="px-4 py-2 bg-stone-800 text-white text-sm hover:bg-stone-700 transition-colors">
            + Tambah Tamu
        </button>
    </div>

    {{-- Stats --}}
    <div class="grid grid-cols-2 sm:grid-cols-4 gap-3 mb-6">
        @php
            $total = $guests->total();
            $checkedIn = $guests->getCollection()->filter(fn($g) => $g->checkin)->count();
            $rsvpYes = $guests->getCollection()->filter(fn($g) => $g->rsvp?->status === 'attending')->count();
        @endphp
        <div class="bg-white border border-stone-200 px-4 py-3">
            <p class="text-2xl font-semibold text-stone-800">{{ $total }}</p>
            <p class="text-xs text-stone-400 mt-0.5">Total Tamu</p>
        </div>
        <div class="bg-white border border-stone-200 px-4 py-3">
            <p class="text-2xl font-semibold text-stone-800">{{ $guests->getCollection()->sum('max_pax') }}</p>
            <p class="text-xs text-stone-400 mt-0.5">Total Kursi</p>
        </div>
        <div class="bg-white border border-stone-200 px-4 py-3">
            <p class="text-2xl font-semibold text-green-600">{{ $rsvpYes }}</p>
            <p class="text-xs text-stone-400 mt-0.5">RSVP Hadir</p>
        </div>
        <div class="bg-white border border-stone-200 px-4 py-3">
            <p class="text-2xl font-semibold text-blue-600">{{ $checkedIn }}</p>
            <p class="text-xs text-stone-400 mt-0.5">Check-in</p>
        </div>
    </div>

    {{-- Filter & Search --}}
    <form method="GET" class="flex gap-2 mb-4">
        <input type="text" name="search" value="{{ request('search') }}"
            placeholder="Cari nama tamu..."
            class="flex-1 border border-stone-200 px-3 py-2 text-sm focus:outline-none focus:border-stone-400">
        <select name="category_id" class="border border-stone-200 px-3 py-2 text-sm focus:outline-none focus:border-stone-400">
            <option value="">Semua Kategori</option>
            @foreach($categories as $cat)
            <option value="{{ $cat->id }}" @selected(request('category_id') == $cat->id)>{{ $cat->name }}</option>
            @endforeach
        </select>
        <button type="submit" class="px-4 py-2 bg-stone-100 text-stone-700 text-sm hover:bg-stone-200 transition-colors">
            Filter
        </button>
        @if(request('search') || request('category_id'))
        <a href="{{ route('admin.weddings.guests.index', $wedding) }}" class="px-4 py-2 text-stone-400 text-sm hover:text-stone-600">
            Reset
        </a>
        @endif
    </form>

    {{-- Import result --}}
    @if(session('import_result'))
    @php $result = session('import_result'); @endphp
    <div class="mb-4 px-4 py-3 border text-sm {{ ($result['failed'] ?? 0) > 0 ? 'bg-amber-50 border-amber-200 text-amber-700' : 'bg-green-50 border-green-200 text-green-700' }}">
        Import selesai: <strong>{{ $result['imported'] ?? 0 }} berhasil</strong>{{ ($result['duplicate'] ?? 0) > 0 ? ', ' . $result['duplicate'] . ' duplikat dilewati' : '' }}{{ ($result['failed'] ?? 0) > 0 ? ', ' . $result['failed'] . ' gagal' : '' }}.{{ ($result['categoriesCreated'] ?? 0) > 0 ? ' ' . $result['categoriesCreated'] . ' kategori baru dibuat otomatis.' : '' }}
    </div>
    @endif

    {{-- Table --}}
    <div class="bg-white border border-stone-200 overflow-hidden">
        @if($guests->isEmpty())
        <div class="px-6 py-12 text-center text-stone-400 text-sm">
            Belum ada tamu.
            @if(request('search') || request('category_id'))
                <a href="{{ route('admin.weddings.guests.index', $wedding) }}" class="underline ml-1">Hapus filter</a>
            @endif
        </div>
        @else
        <table class="w-full text-sm">
            <thead>
                <tr class="border-b border-stone-200 bg-stone-50">
                    <th class="text-left px-4 py-3 font-medium text-stone-500 text-xs uppercase tracking-wide">Nama</th>
                    <th class="text-left px-4 py-3 font-medium text-stone-500 text-xs uppercase tracking-wide hidden sm:table-cell">Tipe</th>
                    <th class="text-left px-4 py-3 font-medium text-stone-500 text-xs uppercase tracking-wide hidden sm:table-cell">Kategori</th>
                    <th class="text-left px-4 py-3 font-medium text-stone-500 text-xs uppercase tracking-wide hidden md:table-cell">Kontak</th>
                    <th class="text-center px-4 py-3 font-medium text-stone-500 text-xs uppercase tracking-wide">Pax</th>
                    <th class="text-center px-4 py-3 font-medium text-stone-500 text-xs uppercase tracking-wide">RSVP</th>
                    <th class="text-center px-4 py-3 font-medium text-stone-500 text-xs uppercase tracking-wide">Check-in</th>
                    <th class="px-4 py-3"></th>
                </tr>
            </thead>
            <tbody class="divide-y divide-stone-100">
                @foreach($guests as $guest)
                <tr class="hover:bg-stone-50 transition-colors" x-data="{ open: false }">
                    <td class="px-4 py-3">
                        <div class="flex items-center gap-1.5">
                            <p class="font-medium text-stone-800">{{ $guest->name }}</p>
                            @if($guest->guest_type === 'vip')
                            <span class="text-xs font-semibold text-amber-600 border border-amber-300 px-1 leading-4">VIP</span>
                            @endif
                        </div>
                        @if($guest->notes)
                        <p class="text-xs text-stone-400 mt-0.5 truncate max-w-[180px]">{{ $guest->notes }}</p>
                        @endif
                    </td>
                    <td class="px-4 py-3 hidden sm:table-cell">
                        @if($guest->guest_type === 'vip')
                        <span class="inline-block px-2 py-0.5 text-xs bg-amber-50 text-amber-600 border border-amber-200">VIP</span>
                        @else
                        <span class="inline-block px-2 py-0.5 text-xs bg-stone-100 text-stone-500">Regular</span>
                        @endif
                    </td>
                    <td class="px-4 py-3 hidden sm:table-cell">
                        @if($guest->category)
                        <span class="inline-block px-2 py-0.5 text-xs bg-stone-100 text-stone-600">{{ $guest->category->name }}</span>
                        @else
                        <span class="text-stone-300">—</span>
                        @endif
                    </td>
                    <td class="px-4 py-3 hidden md:table-cell text-stone-500">
                        {{ $guest->phone ?: ($guest->email ?: '—') }}
                    </td>
                    <td class="px-4 py-3 text-center text-stone-600">{{ $guest->max_pax }}</td>
                    <td class="px-4 py-3 text-center">
                        @if($guest->rsvp)
                            @if($guest->rsvp->status === 'attending')
                            <span class="text-xs text-green-600 font-medium">Hadir</span>
                            @elseif($guest->rsvp->status === 'not_attending')
                            <span class="text-xs text-red-400">Tidak</span>
                            @else
                            <span class="text-xs text-stone-400">Ragu</span>
                            @endif
                        @else
                        <span class="text-stone-300 text-xs">—</span>
                        @endif
                    </td>
                    <td class="px-4 py-3 text-center">
                        @if($guest->checkin)
                        <span class="text-xs text-blue-600 font-medium">✓</span>
                        @else
                        <span class="text-stone-300 text-xs">—</span>
                        @endif
                    </td>
                    <td class="px-4 py-3">
                        <div class="flex items-center justify-end gap-2">
                            {{-- Copy link --}}
                            <button type="button"
                                data-url="{{ $guest->personalUrl() }}"
                                data-couple="{{ $wedding->bride_name }} & {{ $wedding->groom_name }}"
                                data-guest="{{ $guest->name }}"
                                onclick="copyGuestLink(this)"
                                class="text-xs text-stone-400 hover:text-stone-600 transition-colors whitespace-nowrap">
                                Copy Link
                            </button>
                            {{-- QR --}}
                            <a href="{{ route('admin.weddings.guests.qr', [$wedding, $guest]) }}" target="_blank"
                                class="text-xs text-stone-400 hover:text-stone-600 transition-colors">
                                QR
                            </a>
                            <a href="{{ route('admin.weddings.guests.qr.download', [$wedding, $guest]) }}"
                                class="text-xs text-stone-400 hover:text-stone-600 transition-colors">
                                ↓
                            </a>
                            {{-- Edit --}}
                            <button onclick="openEdit({{ $guest->id }}, {{ json_encode($guest->only(['name','phone','email','max_pax','notes','category_id','guest_type'])) }})"
                                class="text-xs text-stone-400 hover:text-stone-600 transition-colors">
                                Edit
                            </button>
                            {{-- More --}}
                            <div class="relative" x-data="{ open: false }">
                                <button @click="open = !open" class="text-stone-300 hover:text-stone-500 transition-colors">
                                    <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                                        <path d="M10 6a2 2 0 110-4 2 2 0 010 4zm0 6a2 2 0 110-4 2 2 0 010 4zm0 6a2 2 0 110-4 2 2 0 010 4z"/>
                                    </svg>
                                </button>
                                <div x-show="open" @click.outside="open = false"
                                    class="absolute right-0 mt-1 w-40 bg-white border border-stone-200 shadow-sm z-10 py-1">
                                    {{-- Regenerate token --}}
                                    <form method="POST" action="{{ route('admin.weddings.guests.regenerate-token', [$wedding, $guest]) }}">
                                        @csrf
                                        <button type="submit" class="w-full text-left px-3 py-1.5 text-xs text-stone-600 hover:bg-stone-50"
                                            onclick="return confirm('Token lama akan langsung invalid. Lanjutkan?')">
                                            Regenerate Token
                                        </button>
                                    </form>
                                    {{-- Delete --}}
                                    <form method="POST" action="{{ route('admin.weddings.guests.destroy', [$wedding, $guest]) }}">
                                        @csrf @method('DELETE')
                                        <button type="submit" class="w-full text-left px-3 py-1.5 text-xs text-red-500 hover:bg-red-50"
                                            onclick="return confirm('Hapus tamu {{ addslashes($guest->name) }}?')">
                                            Hapus
                                        </button>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>

        @if($guests->hasPages())
        <div class="px-4 py-3 border-t border-stone-100">
            {{ $guests->withQueryString()->links() }}
        </div>
        @endif
        @endif
    </div>

    {{-- Import CSV --}}
    <div class="mt-4">
        <details class="group">
            <summary class="text-xs text-stone-400 cursor-pointer hover:text-stone-600 select-none">
                Import CSV
            </summary>
            <form method="POST" action="{{ route('admin.weddings.guests.import', $wedding) }}"
                enctype="multipart/form-data" class="mt-2 flex gap-2 items-center">
                @csrf
                <input type="file" name="file" accept=".csv,.txt" required
                    class="text-xs text-stone-600 file:mr-2 file:px-3 file:py-1.5 file:border file:border-stone-200 file:text-xs file:bg-stone-50 file:text-stone-600 hover:file:bg-stone-100">
                <button type="submit" class="px-3 py-1.5 bg-stone-100 text-stone-700 text-xs hover:bg-stone-200 transition-colors">
                    Upload
                </button>
            </form>
            <p class="mt-1 text-xs text-stone-400">
                Kolom wajib: <code class="bg-stone-100 px-1">name</code>, <code class="bg-stone-100 px-1">max_pax</code> &nbsp;|&nbsp;
                Opsional: <code class="bg-stone-100 px-1">phone</code>, <code class="bg-stone-100 px-1">email</code>, <code class="bg-stone-100 px-1">notes</code>, <code class="bg-stone-100 px-1">category_id</code>, <code class="bg-stone-100 px-1">guest_type</code> (regular/vip)
                &nbsp;|&nbsp;
                <a href="/template-import-tamu.csv" download class="underline hover:text-stone-600">Download Template</a>
            </p>
        </details>
    </div>
</div>

{{-- Modal: Tambah Tamu --}}
<div id="modal-add" class="hidden fixed inset-0 bg-black/40 z-50 flex items-center justify-center p-4">
    <div class="bg-white w-full max-w-md">
        <div class="flex items-center justify-between px-5 py-4 border-b border-stone-200">
            <h2 class="text-sm font-semibold text-stone-800">Tambah Tamu</h2>
            <button onclick="document.getElementById('modal-add').classList.add('hidden')" class="text-stone-400 hover:text-stone-600">✕</button>
        </div>
        <form method="POST" action="{{ route('admin.weddings.guests.store', $wedding) }}" class="px-5 py-4 space-y-3">
            @csrf
            <div>
                <label class="block text-xs text-stone-500 mb-1">Nama <span class="text-red-400">*</span></label>
                <input type="text" name="name" required class="w-full border border-stone-200 px-3 py-2 text-sm focus:outline-none focus:border-stone-400">
            </div>
            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="block text-xs text-stone-500 mb-1">No. HP</label>
                    <input type="text" name="phone" class="w-full border border-stone-200 px-3 py-2 text-sm focus:outline-none focus:border-stone-400">
                </div>
                <div>
                    <label class="block text-xs text-stone-500 mb-1">Email</label>
                    <input type="email" name="email" class="w-full border border-stone-200 px-3 py-2 text-sm focus:outline-none focus:border-stone-400">
                </div>
            </div>
            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="block text-xs text-stone-500 mb-1">Kategori</label>
                    <select name="category_id" class="w-full border border-stone-200 px-3 py-2 text-sm focus:outline-none focus:border-stone-400">
                        <option value="">— Tanpa Kategori —</option>
                        @foreach($categories as $cat)
                        <option value="{{ $cat->id }}">{{ $cat->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-xs text-stone-500 mb-1">Maks. Pax <span class="text-red-400">*</span></label>
                    <input type="number" name="max_pax" value="1" min="1" max="20" required
                        class="w-full border border-stone-200 px-3 py-2 text-sm focus:outline-none focus:border-stone-400">
                </div>
            </div>
            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="block text-xs text-stone-500 mb-1">Tipe Tamu</label>
                    <select name="guest_type" class="w-full border border-stone-200 px-3 py-2 text-sm focus:outline-none focus:border-stone-400">
                        <option value="regular">Regular</option>
                        <option value="vip">VIP</option>
                    </select>
                </div>
            </div>
            <div>
                <label class="block text-xs text-stone-500 mb-1">Catatan</label>
                <textarea name="notes" rows="2" class="w-full border border-stone-200 px-3 py-2 text-sm focus:outline-none focus:border-stone-400 resize-none"></textarea>
            </div>
            <div class="flex justify-end gap-2 pt-1">
                <button type="button" onclick="document.getElementById('modal-add').classList.add('hidden')"
                    class="px-4 py-2 text-sm text-stone-500 hover:text-stone-700">Batal</button>
                <button type="submit" class="px-4 py-2 bg-stone-800 text-white text-sm hover:bg-stone-700 transition-colors">Simpan</button>
            </div>
        </form>
    </div>
</div>

{{-- Modal: Edit Tamu --}}
<div id="modal-edit" class="hidden fixed inset-0 bg-black/40 z-50 flex items-center justify-center p-4">
    <div class="bg-white w-full max-w-md">
        <div class="flex items-center justify-between px-5 py-4 border-b border-stone-200">
            <h2 class="text-sm font-semibold text-stone-800">Edit Tamu</h2>
            <button onclick="document.getElementById('modal-edit').classList.add('hidden')" class="text-stone-400 hover:text-stone-600">✕</button>
        </div>
        <form id="form-edit" method="POST" class="px-5 py-4 space-y-3">
            @csrf @method('PUT')
            <div>
                <label class="block text-xs text-stone-500 mb-1">Nama <span class="text-red-400">*</span></label>
                <input type="text" name="name" id="edit-name" required class="w-full border border-stone-200 px-3 py-2 text-sm focus:outline-none focus:border-stone-400">
            </div>
            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="block text-xs text-stone-500 mb-1">No. HP</label>
                    <input type="text" name="phone" id="edit-phone" class="w-full border border-stone-200 px-3 py-2 text-sm focus:outline-none focus:border-stone-400">
                </div>
                <div>
                    <label class="block text-xs text-stone-500 mb-1">Email</label>
                    <input type="email" name="email" id="edit-email" class="w-full border border-stone-200 px-3 py-2 text-sm focus:outline-none focus:border-stone-400">
                </div>
            </div>
            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="block text-xs text-stone-500 mb-1">Kategori</label>
                    <select name="category_id" id="edit-category" class="w-full border border-stone-200 px-3 py-2 text-sm focus:outline-none focus:border-stone-400">
                        <option value="">— Tanpa Kategori —</option>
                        @foreach($categories as $cat)
                        <option value="{{ $cat->id }}">{{ $cat->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-xs text-stone-500 mb-1">Maks. Pax <span class="text-red-400">*</span></label>
                    <input type="number" name="max_pax" id="edit-max-pax" min="1" max="20" required
                        class="w-full border border-stone-200 px-3 py-2 text-sm focus:outline-none focus:border-stone-400">
                </div>
            </div>
            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="block text-xs text-stone-500 mb-1">Tipe Tamu</label>
                    <select name="guest_type" id="edit-guest-type" class="w-full border border-stone-200 px-3 py-2 text-sm focus:outline-none focus:border-stone-400">
                        <option value="regular">Regular</option>
                        <option value="vip">VIP</option>
                    </select>
                </div>
            </div>
            <div>
                <label class="block text-xs text-stone-500 mb-1">Catatan</label>
                <textarea name="notes" id="edit-notes" rows="2" class="w-full border border-stone-200 px-3 py-2 text-sm focus:outline-none focus:border-stone-400 resize-none"></textarea>
            </div>
            <div class="flex justify-end gap-2 pt-1">
                <button type="button" onclick="document.getElementById('modal-edit').classList.add('hidden')"
                    class="px-4 py-2 text-sm text-stone-500 hover:text-stone-700">Batal</button>
                <button type="submit" class="px-4 py-2 bg-stone-800 text-white text-sm hover:bg-stone-700 transition-colors">Simpan</button>
            </div>
        </form>
    </div>
</div>

<script>
function copyGuestLink(btn) {
    const url    = btn.dataset.url;
    const couple = btn.dataset.couple;
    const guest  = btn.dataset.guest;
    const text =
`Assalamu'alaikum Warahmatullahi Wabarakatuh.

Kepada Yth. Bapak/Ibu/Saudara/i ${guest},

Tanpa mengurangi rasa hormat, perkenankan kami mengundang Bapak/Ibu/Saudara/i untuk hadir serta memberikan doa restu pada acara pernikahan kami:

${couple}

Berikut tautan undangan kami untuk info lengkap acara:
${url}

Merupakan suatu kehormatan dan kebahagiaan bagi kami apabila Bapak/Ibu/Saudara/i berkenan hadir di momen bahagia ini.

Wassalamu'alaikum Warahmatullahi Wabarakatuh.
Hormat kami,
${couple}`;
    navigator.clipboard.writeText(text).then(() => {
        const orig = btn.textContent;
        btn.textContent = 'Copied!';
        setTimeout(() => btn.textContent = orig, 2000);
    });
}

function openEdit(id, data) {
    const base = '{{ url("admin/weddings/" . $wedding->id . "/guests") }}';
    document.getElementById('form-edit').action = base + '/' + id;
    document.getElementById('edit-name').value = data.name || '';
    document.getElementById('edit-phone').value = data.phone || '';
    document.getElementById('edit-email').value = data.email || '';
    document.getElementById('edit-max-pax').value = data.max_pax || 1;
    document.getElementById('edit-notes').value = data.notes || '';
    document.getElementById('edit-category').value = data.category_id || '';
    document.getElementById('edit-guest-type').value = data.guest_type || 'regular';
    document.getElementById('modal-edit').classList.remove('hidden');
}
</script>
@endsection
