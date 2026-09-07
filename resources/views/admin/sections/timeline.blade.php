@extends('admin.layout')

@section('title', 'Timeline — ' . $wedding->coupleName())

@section('content')
<div class="max-w-2xl">
    <div class="flex items-center justify-between mb-6">
        <div>
            <p class="text-xs text-stone-400 mb-0.5">
                <a href="{{ route('admin.weddings.sections.index', $wedding) }}" class="hover:text-stone-600">Sections</a>
                <span class="mx-1">›</span> Timeline
            </p>
            <h1 class="text-xl font-semibold text-stone-800">Isi Timeline</h1>
        </div>
        <button onclick="document.getElementById('modal-add').classList.remove('hidden')"
            class="px-4 py-2 bg-stone-800 text-white text-sm hover:bg-stone-700 transition-colors">
            + Tambah
        </button>
    </div>

    @if(session('success'))
    <div class="mb-4 px-4 py-3 bg-green-50 border border-green-200 text-green-700 text-sm">{{ session('success') }}</div>
    @endif

    @if(empty($items))
    <div class="bg-white border border-stone-200 px-6 py-12 text-center text-stone-400 text-sm">
        Belum ada item timeline. Klik <strong>+ Tambah</strong> untuk mulai.
    </div>
    @else
    <div class="space-y-2">
        @foreach($items as $i => $item)
        <div class="bg-white border border-stone-200 px-5 py-4 flex items-start justify-between gap-4">
            <div class="flex gap-4 flex-1 min-w-0">
                <div class="shrink-0 text-center">
                    <span class="inline-block bg-stone-800 text-white text-xs px-2 py-1 font-mono">{{ $item['time'] }}</span>
                </div>
                <div class="min-w-0">
                    <p class="font-medium text-stone-800">{{ $item['title'] }}</p>
                    @if(!empty($item['description']))
                    <p class="text-sm text-stone-500 mt-0.5">{{ $item['description'] }}</p>
                    @endif
                </div>
            </div>
            <div class="flex items-center gap-2 shrink-0">
                <button onclick="openEdit({{ $i }}, {{ json_encode($item) }})"
                    class="text-xs text-stone-400 hover:text-stone-600 transition-colors">Edit</button>
                <form method="POST" action="{{ route('admin.weddings.sections.timeline.destroy', [$wedding, $section, $i]) }}">
                    @csrf @method('DELETE')
                    <button type="submit" class="text-xs text-red-400 hover:text-red-600 transition-colors"
                        onclick="return confirm('Hapus item ini?')">Hapus</button>
                </form>
            </div>
        </div>
        @endforeach
    </div>
    @endif
</div>

{{-- Modal Add --}}
<div id="modal-add" class="hidden fixed inset-0 bg-black/40 z-50 flex items-center justify-center p-4">
    <div class="bg-white w-full max-w-sm">
        <div class="flex items-center justify-between px-5 py-4 border-b border-stone-200">
            <h2 class="text-sm font-semibold text-stone-800">Tambah Item Timeline</h2>
            <button onclick="document.getElementById('modal-add').classList.add('hidden')" class="text-stone-400 hover:text-stone-600">✕</button>
        </div>
        <form method="POST" action="{{ route('admin.weddings.sections.timeline.store', [$wedding, $section]) }}" class="px-5 py-4 space-y-3">
            @csrf
            <div>
                <label class="block text-xs text-stone-500 mb-1">Waktu <span class="text-red-400">*</span></label>
                <input type="text" name="time" placeholder="cth: 08.00, 09:30 WIB, Pagi" required
                    class="w-full border border-stone-200 px-3 py-2 text-sm focus:outline-none focus:border-stone-400">
            </div>
            <div>
                <label class="block text-xs text-stone-500 mb-1">Judul <span class="text-red-400">*</span></label>
                <input type="text" name="title" placeholder="cth: Akad Nikah" required
                    class="w-full border border-stone-200 px-3 py-2 text-sm focus:outline-none focus:border-stone-400">
            </div>
            <div>
                <label class="block text-xs text-stone-500 mb-1">Keterangan</label>
                <textarea name="description" rows="2" placeholder="Deskripsi singkat (opsional)"
                    class="w-full border border-stone-200 px-3 py-2 text-sm focus:outline-none focus:border-stone-400 resize-none"></textarea>
            </div>
            <div class="flex justify-end gap-2 pt-1">
                <button type="button" onclick="document.getElementById('modal-add').classList.add('hidden')"
                    class="px-4 py-2 text-sm text-stone-500 hover:text-stone-700">Batal</button>
                <button type="submit" class="px-4 py-2 bg-stone-800 text-white text-sm hover:bg-stone-700 transition-colors">Simpan</button>
            </div>
        </form>
    </div>
</div>

{{-- Modal Edit --}}
<div id="modal-edit" class="hidden fixed inset-0 bg-black/40 z-50 flex items-center justify-center p-4">
    <div class="bg-white w-full max-w-sm">
        <div class="flex items-center justify-between px-5 py-4 border-b border-stone-200">
            <h2 class="text-sm font-semibold text-stone-800">Edit Item Timeline</h2>
            <button onclick="document.getElementById('modal-edit').classList.add('hidden')" class="text-stone-400 hover:text-stone-600">✕</button>
        </div>
        <form id="form-edit" method="POST" class="px-5 py-4 space-y-3">
            @csrf @method('PUT')
            <div>
                <label class="block text-xs text-stone-500 mb-1">Waktu <span class="text-red-400">*</span></label>
                <input type="text" name="time" id="edit-time" required
                    class="w-full border border-stone-200 px-3 py-2 text-sm focus:outline-none focus:border-stone-400">
            </div>
            <div>
                <label class="block text-xs text-stone-500 mb-1">Judul <span class="text-red-400">*</span></label>
                <input type="text" name="title" id="edit-title" required
                    class="w-full border border-stone-200 px-3 py-2 text-sm focus:outline-none focus:border-stone-400">
            </div>
            <div>
                <label class="block text-xs text-stone-500 mb-1">Keterangan</label>
                <textarea name="description" id="edit-description" rows="2"
                    class="w-full border border-stone-200 px-3 py-2 text-sm focus:outline-none focus:border-stone-400 resize-none"></textarea>
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
function openEdit(index, data) {
    const base = '{{ url("admin/weddings/{$wedding->id}/sections/{$section->id}/timeline") }}';
    document.getElementById('form-edit').action = base + '/' + index;
    document.getElementById('edit-time').value = data.time || '';
    document.getElementById('edit-title').value = data.title || '';
    document.getElementById('edit-description').value = data.description || '';
    document.getElementById('modal-edit').classList.remove('hidden');
}
</script>
@endsection
