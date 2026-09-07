@extends('admin.layout')

@section('title', 'Kategori Tamu — ' . $wedding->coupleName())

@section('content')
<div class="max-w-2xl">
    <div class="flex items-center justify-between mb-6">
        <div>
            <p class="text-xs text-stone-400 mb-0.5">
                <a href="{{ route('admin.weddings.edit', $wedding) }}" class="hover:text-stone-600">{{ $wedding->coupleName() }}</a>
                <span class="mx-1">›</span> Kategori
            </p>
            <h1 class="text-xl font-semibold text-stone-800">Kategori Tamu</h1>
        </div>
        <button onclick="document.getElementById('modal-add').classList.remove('hidden')"
            class="px-4 py-2 bg-stone-800 text-white text-sm hover:bg-stone-700 transition-colors">
            + Tambah Kategori
        </button>
    </div>

    <div class="bg-white border border-stone-200">
        @if($categories->isEmpty())
        <div class="px-6 py-12 text-center text-stone-400 text-sm">Belum ada kategori.</div>
        @else
        <table class="w-full text-sm">
            <thead>
                <tr class="border-b border-stone-200 bg-stone-50">
                    <th class="text-left px-4 py-3 text-xs font-medium text-stone-500 uppercase tracking-wide">Nama</th>
                    <th class="text-left px-4 py-3 text-xs font-medium text-stone-500 uppercase tracking-wide hidden sm:table-cell">Deskripsi</th>
                    <th class="text-center px-4 py-3 text-xs font-medium text-stone-500 uppercase tracking-wide">Tamu</th>
                    <th class="px-4 py-3"></th>
                </tr>
            </thead>
            <tbody class="divide-y divide-stone-100">
                @foreach($categories as $cat)
                <tr class="hover:bg-stone-50 transition-colors">
                    <td class="px-4 py-3 font-medium text-stone-800">{{ $cat->name }}</td>
                    <td class="px-4 py-3 text-stone-400 hidden sm:table-cell">{{ $cat->description ?: '—' }}</td>
                    <td class="px-4 py-3 text-center text-stone-600">{{ $cat->guests_count }}</td>
                    <td class="px-4 py-3">
                        <div class="flex items-center justify-end gap-3">
                            <button onclick="openEdit({{ $cat->id }}, {{ json_encode(['name'=>$cat->name,'description'=>$cat->description,'sort_order'=>$cat->sort_order]) }})"
                                class="text-xs text-stone-400 hover:text-stone-600 transition-colors">Edit</button>
                            <form method="POST" action="{{ route('admin.weddings.categories.destroy', [$wedding, $cat]) }}">
                                @csrf @method('DELETE')
                                <button type="submit" class="text-xs text-red-400 hover:text-red-600 transition-colors"
                                    onclick="return confirm('Tamu dalam kategori ini akan menjadi tanpa kategori. Lanjutkan?')">Hapus</button>
                            </form>
                        </div>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
        @endif
    </div>
</div>

{{-- Modal Add --}}
<div id="modal-add" class="hidden fixed inset-0 bg-black/40 z-50 flex items-center justify-center p-4">
    <div class="bg-white w-full max-w-sm">
        <div class="flex items-center justify-between px-5 py-4 border-b border-stone-200">
            <h2 class="text-sm font-semibold text-stone-800">Tambah Kategori</h2>
            <button onclick="document.getElementById('modal-add').classList.add('hidden')" class="text-stone-400 hover:text-stone-600">✕</button>
        </div>
        <form method="POST" action="{{ route('admin.weddings.categories.store', $wedding) }}" class="px-5 py-4 space-y-3">
            @csrf
            <div>
                <label class="block text-xs text-stone-500 mb-1">Nama <span class="text-red-400">*</span></label>
                <input type="text" name="name" required class="w-full border border-stone-200 px-3 py-2 text-sm focus:outline-none focus:border-stone-400">
            </div>
            <div>
                <label class="block text-xs text-stone-500 mb-1">Deskripsi</label>
                <input type="text" name="description" class="w-full border border-stone-200 px-3 py-2 text-sm focus:outline-none focus:border-stone-400">
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
            <h2 class="text-sm font-semibold text-stone-800">Edit Kategori</h2>
            <button onclick="document.getElementById('modal-edit').classList.add('hidden')" class="text-stone-400 hover:text-stone-600">✕</button>
        </div>
        <form id="form-edit" method="POST" class="px-5 py-4 space-y-3">
            @csrf @method('PUT')
            <div>
                <label class="block text-xs text-stone-500 mb-1">Nama <span class="text-red-400">*</span></label>
                <input type="text" name="name" id="edit-name" required class="w-full border border-stone-200 px-3 py-2 text-sm focus:outline-none focus:border-stone-400">
            </div>
            <div>
                <label class="block text-xs text-stone-500 mb-1">Deskripsi</label>
                <input type="text" name="description" id="edit-description" class="w-full border border-stone-200 px-3 py-2 text-sm focus:outline-none focus:border-stone-400">
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
function openEdit(id, data) {
    const base = '{{ url("admin/weddings/" . $wedding->id . "/categories") }}';
    document.getElementById('form-edit').action = base + '/' + id;
    document.getElementById('edit-name').value = data.name || '';
    document.getElementById('edit-description').value = data.description || '';
    document.getElementById('modal-edit').classList.remove('hidden');
}
</script>
@endsection
